<?php
// Replace app/Models/Supply.php with this

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Supply extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'supplies';

    protected $fillable = [
    'name', 'category', 'brand', 'unit', 'supplier_id',
    'quantity_purchased', 'quantity_remaining', 'damaged_stock', 'purchase_price', 'shipping_cost',
    'cost_per_unit', 'reorder_level', 'purchase_date', 'notes',
];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity_purchased' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
         'damaged_stock' => 'decimal:2',
        'cost_per_unit' => 'decimal:4', // was decimal:2 — rounded cheap-per-unit items to 0
        'reorder_level' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryTransactions()
    {
        return $this->morphMany(InventoryTransaction::class, 'trackable');
    }

    public function isLow(): bool
    {
        if (! is_null($this->reorder_level)) {
            return (float) $this->quantity_remaining <= (float) $this->reorder_level;
        }

        if ((float) $this->quantity_purchased <= 0) {
            return false;
        }

        return ((float) $this->quantity_remaining / (float) $this->quantity_purchased) <= 0.15;
    }

    public function hasDamagedStock(): bool
{
    return (float) $this->damaged_stock > 0;
}

    public function percentRemaining(): float
    {
        if ((float) $this->quantity_purchased <= 0) {
            return 0;
        }

        $percent = ((float) $this->quantity_remaining / (float) $this->quantity_purchased) * 100;

        return max(0, min($percent, 100));
    }

    /**
     * Recalculate cost_per_unit. purchase_price is what you pay for ONE unit
     * (not a batch total) — so landed cost/unit is just that price plus the
     * shipping cost spread evenly across everything you bought.
     */
    public function recalculateCostPerUnit(): void
    {
        $shippingPerUnit = (float) $this->quantity_purchased > 0
            ? bcdiv((string) $this->shipping_cost, (string) $this->quantity_purchased, 4)
            : '0';

        $this->cost_per_unit = bcadd((string) $this->purchase_price, $shippingPerUnit, 4);
    }

    /**
     * Total amount paid for the whole batch: unit price × quantity + shipping.
     * Useful for a detail/show page or expense reporting.
     */
    public function totalCost(): string
    {
        $subtotal = bcmul((string) $this->purchase_price, (string) $this->quantity_purchased, 2);

        return bcadd($subtotal, (string) $this->shipping_cost, 2);
    }
}