<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'category_id',
        'vendor_name',
        'contact_person',
        'mobile',
        'email',
        'address',
        'gst_number',
        'pan_number',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(VendorCategory::class, 'category_id');
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class, 'vendor_id');
    }

    public function getCashPaidForProject($projectId)
    {
        return (float) $this->payments()->where('project_id', $projectId)->where('payment_mode', 'Cash')->sum('amount');
    }

    public function getChequePaidForProject($projectId)
    {
        return (float) $this->payments()->where('project_id', $projectId)->where('payment_mode', 'Cheque')->sum('amount');
    }

    public function getTotalPaidForProject($projectId)
    {
        return (float) $this->payments()->where('project_id', $projectId)->sum('amount');
    }

    public function getTotalBilledForProject($projectId)
    {
        return (float) $this->payments()->where('project_id', $projectId)->sum('invoice_bill_amount');
    }

    public function getRemainingDueForProject($projectId)
    {
        return max(0, $this->getTotalBilledForProject($projectId) - $this->getTotalPaidForProject($projectId));
    }
}
