<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelPartnerContact extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'channel_partner_id',
        'name',
        'mobile',
        'email',
        'designation',
        'is_primary',
        'user_id',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function partner()
    {
        return $this->belongsTo(ChannelPartner::class, 'channel_partner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
