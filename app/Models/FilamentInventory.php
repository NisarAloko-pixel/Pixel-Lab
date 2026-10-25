<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class FilamentInventory extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = "filament_inventory";

    protected $fillable = [
        "name", "brand", "type", "color", "supplier_id",
        "weight_purchased_g", "weight_remaining_g", "purchase_price", "shipping_cost",
        "cost_per_kg", "purchase_date",
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'weight_purchased_g' => 'decimal:2',
        'weight_remaining_g' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryTransactions()
    {
        return $this->morphMany(InventoryTransaction::class, "trackable");
    }

    public function costPerGram(): string
    {
        return bcdiv((string) $this->cost_per_kg, "1000", 6);
    }
}