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
        'channel_partner_id',
        'project_id',
        'lead_id',
        'unit_ids',
        'sales_agent_id',
        'booking_date',
        'quoted_price',
        'agreed_price',
        'discount_amount',
        'charges',
        'tax_amount',
        'total_amount',
        'booking_amount_paid',
        'payment_mode',
        'payment_reference',
        'status',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
        'cancellation_refund_amount',
        'terms_conditions',
        'document_path',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'unit_ids' => 'array',
        'charges' => 'array',
        'quoted_price' => 'decimal:2',
        'agreed_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'booking_amount_paid' => 'decimal:2',
        'cancellation_refund_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $year = date('Y');
                $count = static::where('company_id', $booking->company_id)->count() + 1;
                $booking->booking_number = 'BKG-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function channelPartner()
    {
        return $this->belongsTo(ChannelPartner::class);
    }

    public function salesAgent()
    {
        return $this->belongsTo(User::class, 'sales_agent_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
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
