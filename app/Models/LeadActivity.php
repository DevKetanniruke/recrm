<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use HasFactory, BelongsToCompany, Auditable;

    protected $fillable = [
        'company_id',
        'lead_id',
        'user_id',
        'activity_type',
        'subject',
        'summary',
        'outcome',
        'next_action',
        'scheduled_at',
        'completed_at',
        'next_followup_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_followup_at' => 'datetime',
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
