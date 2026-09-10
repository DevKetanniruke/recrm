<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'booking_id',
        'channel_partner_id',
        'commission_structure_id',
        'agreement_value',
        'commission_percentage',
        'calculated_commission_amount',
        'approved_commission_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'approved_by_user_id',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'agreement_value' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'calculated_commission_amount' => 'decimal:2',
        'approved_commission_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function channelPartner()
    {
        return $this->belongsTo(ChannelPartner::class);
    }

    public function structure()
    {
        return $this->belongsTo(CommissionStructure::class, 'commission_structure_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function payouts()
    {
        return $this->hasMany(CommissionPayout::class);
    }
}
