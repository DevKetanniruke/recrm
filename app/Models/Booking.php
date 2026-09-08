<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'booking_number',
        'unit_id',
        'customer_id',
        'sales_agent_id',
        'booking_date',
        'agreed_price',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'booking_amount_paid',
        'status',
        'cancellation_reason',
        'cancellation_refund_amount',
        'terms_conditions',
        'document_path',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'agreed_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'booking_amount_paid' => 'decimal:2',
        'cancellation_refund_amount' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesAgent()
    {
        return $this->belongsTo(User::class, 'sales_agent_id');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->where('status', 'Verified')->sum('amount_paid');
    }

    public function balanceDue(): float
    {
        return (float) ($this->total_amount - $this->totalPaid());
    }
}
