<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDemandNotice extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'demand_number',
        'booking_id',
        'customer_id',
        'payment_schedule_id',
        'demand_date',
        'due_date',
        'demand_amount',
        'penalty_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'demand_date' => 'date',
        'due_date' => 'date',
        'demand_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($demand) {
            if (empty($demand->demand_number)) {
                $year = date('Y');
                $count = static::where('company_id', $demand->company_id)->count() + 1;
                $demand->demand_number = 'DEM-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
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
}
