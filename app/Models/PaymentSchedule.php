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
        'milestone_code',
        'description',
        'due_date',
        'percentage',
        'amount_due',
        'amount_paid',
        'outstanding_amount',
        'status',
        'overdue_days',
        'penalty_amount',
    ];

    protected $casts = [
        'due_date' => 'date',
        'percentage' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'overdue_days' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            PaymentAllocation::class,
            'payment_schedule_id',
            'id',
            'id',
            'payment_id'
        );
    }

    public function demandNotices()
    {
        return $this->hasMany(PaymentDemandNotice::class);
    }

    public function recalculateOutstanding(): void
    {
        $this->outstanding_amount = max(0, $this->amount_due - $this->amount_paid);

        if ($this->amount_paid >= $this->amount_due) {
            $this->status = 'Paid';
            $this->overdue_days = 0;
        } elseif ($this->amount_paid > 0) {
            $this->status = 'Partially Paid';
        } else {
            if ($this->due_date && $this->due_date->isPast()) {
                $this->status = 'Overdue';
                $this->overdue_days = (int) $this->due_date->diffInDays(now());
            } else {
                $this->status = 'Pending';
                $this->overdue_days = 0;
            }
        }

        $this->save();
    }
}
