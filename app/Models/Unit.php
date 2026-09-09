<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, BelongsToCompany, Auditable, SoftDeletes;

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
        'possession_status',
        'inventory_status',
        'unit_type',
        'carpet_area_sqft',
        'super_builtup_area_sqft',
        'base_rate_per_sqft',
        'total_price',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            if ($unit->floor_id) {
                $floor = Floor::with(['wing.building'])->find($unit->floor_id);
                if ($floor && $floor->wing) {
                    $unit->wing_id = $unit->wing_id ?? $floor->wing_id;
                    $unit->building_id = $unit->building_id ?? $floor->wing->building_id;
                    $unit->project_id = $unit->project_id ?? $floor->wing->building->project_id;
                }
            }

            if (empty($unit->inventory_status)) {
                $unit->inventory_status = $unit->status ?? 'Available';
            }
            if (empty($unit->status)) {
                $unit->status = $unit->inventory_status ?? 'Available';
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function wing(): BelongsTo
    {
        return $this->belongsTo(Wing::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class);
    }

    public function pricing(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UnitPricing::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(UnitStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class)->orderBy('created_at', 'desc');
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
