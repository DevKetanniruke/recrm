<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPayout extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'commission_id',
        'payout_number',
        'amount',
        'payment_date',
        'payment_mode',
        'payment_reference',
        'bank_details',
        'notes',
        'processed_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payout) {
            if (empty($payout->payout_number)) {
                $year = date('Y');
                $count = static::where('company_id', $payout->company_id)->count() + 1;
                $payout->payout_number = 'PAYOUT-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
