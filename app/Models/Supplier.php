<?php
// Place in app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ["name", "contact_person", "phone", "email", "address", "notes"];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /** Non-cancelled purchases; reuses the loaded relation when available. */
    private function activePurchases()
    {
        $purchases = $this->relationLoaded('purchases')
            ? $this->purchases
            : $this->purchases()->get();

        return $purchases->where('status', '!=', 'cancelled');
    }

    /** Total of all non-cancelled purchases (items + shipping), in base currency. */
    public function getTotalPurchasesAttribute(): string
    {
        return $this->activePurchases()
            ->reduce(fn ($carry, $p) => bcadd($carry, $p->total_cost_base, 2), '0.00');
    }

    /** Amount actually paid to this supplier, in base currency. */
    public function getTotalPaidAttribute(): string
    {
        return $this->activePurchases()
            ->reduce(fn ($carry, $p) => bcadd($carry, (string) $p->amount_paid, 2), '0.00');
    }

    /** Positive = you owe the supplier. Negative = you overpaid (credit). */
    public function getOutstandingBalanceAttribute(): string
    {
        return bcsub($this->total_purchases, $this->total_paid, 2);
    }
}