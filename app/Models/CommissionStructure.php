<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionStructure extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'project_id',
        'unit_type_id',
        'name',
        'calculation_type',
        'rate',
        'fixed_amount',
        'slabs_json',
        'milestone_triggers_json',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'slabs_json' => 'array',
        'milestone_triggers_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
}
