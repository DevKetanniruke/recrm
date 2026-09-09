<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCommunicationPreference extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'lead_id',
        'recipient',
        'channel',
        'opt_in_marketing',
        'opt_out_reason',
        'opted_out_at',
    ];

    protected $casts = [
        'opt_in_marketing' => 'boolean',
        'opted_out_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
