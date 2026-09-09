<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_number',
        'lead_id',
        'first_name',
        'middle_name',
        'last_name',
        'mobile',
        'alternate_mobile',
        'email',
        'date_of_birth',
        'occupation',
        'company_or_employer',
        'nationality',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'PAN',
        'pan_number',
        'tax_id',
        'reference',
        'communication_preference',
        'zip_code',
        'kyc_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_number)) {
                $year = date('Y');
                $count = static::where('company_id', $customer->company_id)->count() + 1;
                $customer->customer_number = 'CUST-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
            if (empty($customer->phone) && !empty($customer->mobile)) {
                $customer->phone = $customer->mobile;
            }
            if (empty($customer->mobile) && !empty($customer->phone)) {
                $customer->mobile = $customer->phone;
            }
            if (empty($customer->phone) && empty($customer->mobile)) {
                $customer->phone = '0000000000';
            }
        });
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function coApplicants()
    {
        return $this->hasMany(CoApplicant::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $parts);
    }

    public function getPrimaryMobileAttribute(): string
    {
        return $this->mobile ?: ($this->phone ?: '');
    }
}
