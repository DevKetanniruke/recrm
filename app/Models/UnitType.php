<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use HasFactory, BelongsToCompany, Auditable;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'category',
        'default_carpet_area',
        'attributes',
    ];

    protected $casts = [
        'default_carpet_area' => 'decimal:2',
        'attributes' => 'array',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
