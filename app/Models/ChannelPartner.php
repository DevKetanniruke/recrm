<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelPartner extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'partner_code',
        'company_name',
        'contact_person',
        'mobile',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'gst_number',
        'pan_number',
        'rera_registration_number',
        'bank_account_details_json',
        'status',
        'onboarding_date',
    ];

    protected $casts = [
        'bank_account_details_json' => 'array',
        'onboarding_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($partner) {
            if (empty($partner->partner_code)) {
                $year = date('Y');
                $count = static::where('company_id', $partner->company_id)->count() + 1;
                $partner->partner_code = 'CP-' . $year . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function contacts()
    {
        return $this->hasMany(ChannelPartnerContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ChannelPartnerContact::class)->where('is_primary', true);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
}
