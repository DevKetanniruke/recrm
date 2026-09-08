<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'rate_per_sqft',
        'base_price',
        'floor_rise_rate',
        'facing_premium',
        'plc_amount',
        'parking_charges',
        'clubhouse_charges',
        'infrastructure_charges',
        'maintenance_deposit',
        'legal_charges',
        'gst_percent',
        'other_charges',
        'discount_amount',
        'calculated_total_price',
    ];

    protected $casts = [
        'rate_per_sqft' => 'decimal:2',
        'base_price' => 'decimal:2',
        'floor_rise_rate' => 'decimal:2',
        'facing_premium' => 'decimal:2',
        'plc_amount' => 'decimal:2',
        'parking_charges' => 'decimal:2',
        'clubhouse_charges' => 'decimal:2',
        'infrastructure_charges' => 'decimal:2',
        'maintenance_deposit' => 'decimal:2',
        'legal_charges' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'calculated_total_price' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
