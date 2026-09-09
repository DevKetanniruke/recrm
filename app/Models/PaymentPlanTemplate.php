<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanTemplate extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'milestones_json',
        'is_active',
    ];

    protected $casts = [
        'milestones_json' => 'array',
        'is_active' => 'boolean',
    ];
}
