<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoApplicant extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'customer_name',
        'relationship',
        'mobile',
        'email',
        'ownership_percentage',
        'applicant_type',
        'pan_number',
        'aadhaar_number',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
