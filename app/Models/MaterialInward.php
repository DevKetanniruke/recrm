<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialInward extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'stock_id',
        'category_id',
        'material_name',
        'qty_received',
        'unit_of_measure',
        'unit_cost',
        'total_cost',
        'vendor_id',
        'invoice_number',
        'gate_pass_number',
        'received_date',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'qty_received' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'received_date' => 'date',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(MaterialStock::class, 'stock_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function supplierVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
