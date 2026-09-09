<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, BelongsToCompany, Auditable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'offer_number',
        'lead_id',
        'unit_id',
        'created_by',
        'approved_by',
        'original_unit_price',
        'offered_price',
        'discount_amount',
        'discount_percentage',
        'token_amount_offered',
        'payment_plan_type',
        'valid_until',
        'unit_lock_expires_at',
        'status',
        'approval_notes',
        'rejection_reason',
        'terms_conditions',
        'pdf_path',
    ];

    protected $casts = [
        'original_unit_price' => 'decimal:2',
        'offered_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'token_amount_offered' => 'decimal:2',
        'valid_until' => 'datetime',
        'unit_lock_expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($offer) {
            if (empty($offer->offer_number)) {
                $maxId = static::where('company_id', $offer->company_id)->max('id') + 1;
                $offer->offer_number = 'OFF-' . date('Y') . '-' . str_pad((string) $maxId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function negotiationRounds(): HasMany
    {
        return $this->hasMany(OfferNegotiationRound::class)->orderBy('round_number', 'asc');
    }

    public function isExpired(): bool
    {
        return $this->valid_until->isPast() || ($this->unit_lock_expires_at && $this->unit_lock_expires_at->isPast());
    }
}
