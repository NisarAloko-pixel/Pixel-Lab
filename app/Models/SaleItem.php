<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleItem extends Model
{
     use SoftDeletes;
    protected $fillable = [
        "sale_id", "product_id", "printing_job_id", "description", "quantity",
        "unit_price", "unit_cost", "extra_cost", "extra_items", "discount", "line_total", "is_free",
    ];

    protected $casts = [
        'extra_items' => 'array',
        'is_free' => 'boolean',
    ];

    /**
     * A line counts as free when it was saved with the Free tick, or when it has no price
     * and no total (a gift). The second rule means old lines and lines saved before the
     * is_free column existed are still shown as free everywhere $item->is_free is read.
     */
    public function getIsFreeAttribute($value): bool
    {
        if ((bool) $value) {
            return true;
        }

        return (float) $this->unit_price == 0.0 && (float) $this->line_total == 0.0;
    }

    public function getLineCostAttribute(): string
    {
        $baseCost = bcmul((string) $this->quantity, (string) $this->unit_cost, 2);
        return bcadd($baseCost, (string) $this->extra_cost, 2);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function printingJob()
    {
        return $this->belongsTo(PrintingJob::class);
    }

    public function getLineProfitAttribute(): string
    {
        return bcsub((string) $this->line_total, $this->line_cost, 2);
    }
}