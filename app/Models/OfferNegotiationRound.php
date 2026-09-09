<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferNegotiationRound extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'offer_id',
        'round_number',
        'offered_by',
        'proposed_by_user_id',
        'proposed_price',
        'discount_amount',
        'requested_token_amount',
        'payment_terms',
        'comments',
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'requested_token_amount' => 'decimal:2',
        'round_number' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function proposedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }
}
