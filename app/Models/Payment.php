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
        'payment_number',
        'booking_id',
        'customer_id',
        'payment_schedule_id',
        'receipt_number',
        'amount_paid',
        'payment_date',
        'payment_method',
        'payment_mode',
        'transaction_reference',
        'bank_cheque_number',
        'status',
        'is_reversed',
        'reversal_reason',
        'reversed_at',
        'reversed_by_user_id',
        'received_by',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'reversed_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'is_reversed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $year = date('Y');
                $count = static::where('company_id', $payment->company_id)->count() + 1;
                $payment->payment_number = 'PAY-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
            if (empty($payment->receipt_number)) {
                $year = date('Y');
                $count = static::where('company_id', $payment->company_id)->count() + 1;
                $payment->receipt_number = 'REC-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by_user_id');
    }
}
