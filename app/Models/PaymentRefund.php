<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'payment_id',
        'booking_id',
        'refund_number',
        'refund_amount',
        'refund_date',
        'reason',
        'processed_by_user_id',
        'status',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'refund_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $year = date('Y');
                $count = static::where('company_id', $refund->company_id)->count() + 1;
                $refund->refund_number = 'RFD-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
