<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Auditable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        "sku", "name", "category_id", "filament_inventory_id", "description", "selling_price",
        "cost_price", "current_stock", "min_stock_level", "unit", "grams_used", "print_hours",
        "electricity_rate_per_hour", "prime_tower_grams", "units_per_plate",
        "image_path", "is_active", "notes",
    ];

    protected $casts = [
        "selling_price" => "decimal:2",
        "cost_price" => "decimal:2",
        "current_stock" => "decimal:3",
        "min_stock_level" => "decimal:3",
        "electricity_rate_per_hour" => "decimal:2",
        "prime_tower_grams" => "decimal:2",
        "units_per_plate" => "integer",
        "is_active" => "boolean",
    ];

    public function filament()
    {
        return $this->belongsTo(FilamentInventory::class, 'filament_inventory_id');
    }

    public function filaments()
    {
        return $this->hasMany(ProductFilament::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryTransactions()
    {
        return $this->morphMany(InventoryTransaction::class, "trackable");
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock_level;
    }

    public function getPrintHoursFormattedAttribute(): string
    {
        $totalMinutes = round(($this->print_hours ?? 0) * 60);
        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;
        return "{$h}h {$m}m";
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn("current_stock", "<=", "min_stock_level");
    }

    public function hasDamagedStock(): bool
    {
        return (float) $this->damaged_stock > 0;
    }

    /**
     * Pieces made so far: in stock + damaged + sold. Cancelled sales are excluded
     * (their stock was returned) and soft-deleted sales are excluded automatically.
     * Used to work out how much filament to return/deduct when a recipe is changed.
     */
    public function getUnitsProducedAttribute(): float
    {
        $sold = $this->saleItems()
            ->whereHas('sale', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->sum('quantity');

        return (float) $this->current_stock + (float) $this->damaged_stock + (float) $sold;
    }
}