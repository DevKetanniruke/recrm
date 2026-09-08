<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'booking_id',
        'milestone_name',
        'due_date',
        'amount_due',
        'amount_paid',
        'status',
        'penalty_amount',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
