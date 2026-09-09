<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'lead_id',
        'campaign_id',
        'communication_template_id',
        'channel',
        'recipient',
        'subject',
        'message_body',
        'status',
        'is_transactional',
        'provider_name',
        'provider_reference',
        'failure_reason',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'is_transactional' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'communication_template_id');
    }
}
