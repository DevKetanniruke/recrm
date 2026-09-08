<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    use HasFactory, BelongsToCompany, Auditable;

    protected $fillable = [
        'company_id',
        'name',
        'color_code',
        'sort_order',
        'is_won',
        'is_lost',
        'is_active',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'status_id');
    }
}
