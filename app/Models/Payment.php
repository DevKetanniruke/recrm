<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'booking_id',
        'payment_schedule_id',
        'receipt_number',
        'amount_paid',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'status',
        'received_by',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
