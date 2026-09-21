<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'building_id',
        'category_id',
        'material_name',
        'quantity',
        'unit_of_measure',
        'unit_cost',
        'total_cost',
        'entry_date',
        'supplier_vendor_id',
        'invoice_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'entry_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($entry) {
            $entry->total_cost = (float) $entry->quantity * (float) $entry->unit_cost;
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function supplierVendor()
    {
        return $this->belongsTo(Vendor::class, 'supplier_vendor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
