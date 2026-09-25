<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialStock extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'category_id',
        'material_name',
        'unit_of_measure',
        'current_stock_qty',
        'min_threshold_qty',
        'unit_cost',
        'last_replenished_at',
        'notes',
    ];

    protected $casts = [
        'current_stock_qty' => 'decimal:3',
        'min_threshold_qty' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'last_replenished_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function inwards(): HasMany
    {
        return $this->hasMany(MaterialInward::class, 'stock_id');
    }

    /**
     * Check if item is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->current_stock_qty <= 0;
    }

    /**
     * Check if stock is low below safety threshold.
     */
    public function isLowStock(): bool
    {
        return $this->current_stock_qty > 0 && $this->current_stock_qty <= $this->min_threshold_qty;
    }

    /**
     * Get visual status badge class & label.
     */
    public function getStockStatusAttribute(): array
    {
        if ($this->isOutOfStock()) {
            return [
                'label' => 'Out of Stock',
                'class' => 'bg-danger text-white',
                'pill' => 'danger',
                'percentage' => 0,
            ];
        }

        if ($this->isLowStock()) {
            return [
                'label' => 'Low Stock Alert',
                'class' => 'bg-warning text-dark',
                'pill' => 'warning',
                'percentage' => round(($this->current_stock_qty / max(1, $this->min_threshold_qty * 2)) * 100),
            ];
        }

        $pct = min(100, round(($this->current_stock_qty / max(1, $this->min_threshold_qty * 2)) * 100));

        return [
            'label' => 'Sufficient Stock',
            'class' => 'bg-success text-white',
            'pill' => 'success',
            'percentage' => $pct,
        ];
    }
}
