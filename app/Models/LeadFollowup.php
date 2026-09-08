<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowup extends Model
{
    use HasFactory, BelongsToCompany, Auditable;

    protected $fillable = [
        'company_id',
        'lead_id',
        'user_id',
        'type',
        'followup_at',
        'notes',
        'outcome',
        'next_action',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'followup_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
