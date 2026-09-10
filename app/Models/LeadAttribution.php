<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAttribution extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'lead_id',
        'previous_channel_partner_id',
        'new_channel_partner_id',
        'changed_by_user_id',
        'reason',
        'attributed_at',
    ];

    protected $casts = [
        'attributed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function previousPartner()
    {
        return $this->belongsTo(ChannelPartner::class, 'previous_channel_partner_id');
    }

    public function newPartner()
    {
        return $this->belongsTo(ChannelPartner::class, 'new_channel_partner_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
