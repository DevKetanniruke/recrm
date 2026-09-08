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
        'lead_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'pan_number',
        'tax_id',
        'address',
        'city',
        'state',
        'zip_code',
        'kyc_status',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
