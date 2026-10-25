<?php
// Place in app/Models/Purchase.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;

class Purchase extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'item_type', 'supply_id', 'name', 'category', 'unit',
        'filament_type', 'filament_color', 'filament_brand',
        'supplier_id', 'quantity', 'total_price', 'shipping_cost',
        'order_date', 'status', 'arrived_at', 'arrived_location', 'notes',
        'price_per_unit',
        'currency',
        'exchange_rate',
        'amount_paid',
        'cancellation_reason',
        'cancelled_at',
        'stock_applied',
        'stock_applied_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'order_date' => 'date',
        'arrived_at' => 'datetime',
        'price_per_unit' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'amount_paid' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'stock_applied'    => 'boolean',
    'stock_applied_at' => 'datetime',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isArrived(): bool
    {
        return $this->status === 'arrived';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /** Display name regardless of item_type, for the purchases list. */
    public function displayName(): string
    {
        if ($this->item_type === 'filament') {
            return trim(($this->filament_brand ?? '').' '.$this->filament_type.' '.$this->filament_color);
        }

        return $this->supply?->name ?? $this->name ?? '—';
    }

    public function getTotalPriceBaseAttribute(): float
    {
        return round((float) $this->total_price * (float) $this->exchange_rate, 2);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'reference');
    }

    /** Total cost incl. shipping, in base currency. */
    public function getTotalCostBaseAttribute(): string
    {
        return bcmul(
            bcadd((string) $this->total_price, (string) $this->shipping_cost, 2),
            (string) $this->exchange_rate,
            2
        );
    }

    /** What you still owe the supplier on this purchase, in base currency. */
    public function getAmountDueBaseAttribute(): string
    {
        if ($this->isCancelled()) {
            return '0.00';
        }

        $due = bcsub($this->total_cost_base, (string) $this->amount_paid, 2);

        return bccomp($due, '0', 2) > 0 ? $due : '0.00';
    }

    
}