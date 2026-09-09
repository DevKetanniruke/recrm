<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisit extends Model
{
    use HasFactory, BelongsToCompany, Auditable;

    protected $fillable = [
        'company_id',
        'visit_number',
        'lead_id',
        'project_id',
        'assigned_to',
        'transportation_type',
        'driver_name',
        'driver_phone',
        'pickup_location',
        'pickup_time',
        'visit_date',
        'check_in_at',
        'check_out_at',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'units_viewed',
        'executive_notes',
        'status',
        'feedback',
        'rating',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'pickup_time' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_lat' => 'decimal:8',
        'check_in_lng' => 'decimal:8',
        'check_out_lat' => 'decimal:8',
        'check_out_lng' => 'decimal:8',
        'units_viewed' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visit) {
            if (empty($visit->visit_number)) {
                $maxId = static::where('company_id', $visit->company_id)->max('id') + 1;
                $visit->visit_number = 'SV-' . date('Y') . '-' . str_pad((string) $maxId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
