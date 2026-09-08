<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, BelongsToCompany, SoftDeletes, Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'building_id',
        'wing_id',
        'floor_id',
        'unit_type_id',
        'unit_number',
        'unit_code',
        'carpet_area',
        'built_up_area',
        'super_built_up_area',
        'balcony_area',
        'terrace_area',
        'bedrooms',
        'bathrooms',
        'facing',
        'parking',
        'RERA_unit_number',
        'possession_status',
        'inventory_status',
        // Backward compatibility
        'unit_type',
        'carpet_area_sqft',
        'super_builtup_area_sqft',
        'base_rate_per_sqft',
        'total_price',
        'status',
    ];

    protected $casts = [
        'carpet_area' => 'decimal:2',
        'built_up_area' => 'decimal:2',
        'super_built_up_area' => 'decimal:2',
        'balcony_area' => 'decimal:2',
        'terrace_area' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->floor_id && (! $model->wing_id || ! $model->building_id || ! $model->project_id)) {
                $floor = Floor::with('wing.building')->find($model->floor_id);
                if ($floor && $floor->wing) {
                    $model->wing_id = $model->wing_id ?? $floor->wing_id;
                    $model->building_id = $model->building_id ?? $floor->wing->building_id;
                    $model->project_id = $model->project_id ?? $floor->wing->building->project_id;
                }
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function wing()
    {
        return $this->belongsTo(Wing::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    public function pricing()
    {
        return $this->hasOne(UnitPricing::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(UnitStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function updateInventoryStatus(string $newStatus, ?string $reason = null, ?int $userId = null): void
    {
        $prevStatus = $this->inventory_status ?? $this->status;

        $this->update([
            'inventory_status' => $newStatus,
            'status' => $newStatus,
        ]);

        UnitStatusHistory::create([
            'unit_id' => $this->id,
            'previous_status' => $prevStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
