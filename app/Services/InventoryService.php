<?php

namespace App\Services;

use App\Models\FilamentInventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * All stock movements for products, filament, and supplies go through here, and every
 * movement writes an append-only inventory_transactions row before the cached stock
 * column is touched. Never call ->decrement()/->increment() on stock columns directly
 * elsewhere: that would leave the ledger unable to reconstruct the current balance.
 *
 * Note: damaged_stock on Product is a separate cached column that record() does NOT
 * track (record() only ever moves current_stock / weight_remaining_g / quantity_remaining).
 * markDamaged() and restoreDamaged() below update damaged_stock explicitly, alongside the
 * ledgered current_stock change.
 */
class InventoryService
{
    /**
     * @param Model $trackable Product, FilamentInventory, or Supply instance
     * @param float $quantity signed: positive increases stock, negative decreases it
     */
    public function record(
        Model $trackable,
        string $type,
        float $quantity,
        ?float $unitCost = null,
        ?Model $reference = null,
        ?string $notes = null
    ): InventoryTransaction {
        $stockColumn = $this->stockColumnFor($trackable);

        $newBalance = bcadd((string) $trackable->{$stockColumn}, (string) $quantity, 3);

        if ((float) $newBalance < 0 && $type !== "adjustment") {
            throw new \RuntimeException("Insufficient stock for {$trackable->name}: cannot go below zero.");
        }

        $transaction = InventoryTransaction::create([
            "trackable_type" => get_class($trackable),
            "trackable_id" => $trackable->getKey(),
            "type" => $type,
            "quantity" => $quantity,
            "unit_cost" => $unitCost,
            "reference_type" => $reference ? get_class($reference) : null,
            "reference_id" => $reference?->getKey(),
            "notes" => $notes,
            "created_by" => Auth::id(),
        ]);

        $trackable->{$stockColumn} = $newBalance;
        $trackable->save();

        return $transaction;
    }

    /** Which cached column on the trackable holds its current on-hand quantity. */
    private function stockColumnFor(Model $trackable): string
    {
        return match (true) {
            $trackable instanceof FilamentInventory => "weight_remaining_g",
            $trackable instanceof Supply => "quantity_remaining",
            default => "current_stock",
        };
    }

    public function increaseProductStock(Product $product, float $quantity, ?float $unitCost, ?Model $reference, ?string $notes = null): void
    {
        $this->record($product, "purchase", abs($quantity), $unitCost, $reference, $notes);
    }

    public function decreaseProductStockForSale(Product $product, float $quantity, ?Model $reference, ?string $notes = null): void
    {
        $this->record($product, "sale", -abs($quantity), $product->cost_price, $reference, $notes);
    }

    /**
     * Deducts supply stock consumed by a sale line's "extra item" usage, the same way
     * decreaseProductStockForSale() does for products. Uses the supply's current
     * cost_per_unit as the ledgered unit cost.
     */
    public function decreaseSupplyStockForSale(Supply $supply, float $quantity, ?Model $reference, ?string $notes = null): void
    {
        $this->record($supply, "sale", -abs($quantity), (float) $supply->cost_per_unit, $reference, $notes);
    }

    /**
     * Moves units out of sellable current_stock and into damaged_stock. The ledger
     * (via record()) only ever tracks current_stock, so damaged_stock — a separate
     * cached column, not itself ledgered — must be updated here explicitly.
     */
    public function markDamaged(Product $product, float $quantity, ?string $notes = null): void
    {
        $this->record($product, "damage", -abs($quantity), $product->cost_price, null, $notes);

        $product->damaged_stock = bcadd((string) $product->damaged_stock, (string) abs($quantity), 3);
        $product->save();
    }

    /**
     * Moves units back out of damaged_stock into sellable current_stock.
     */
    public function restoreDamaged(Product $product, float $quantity, ?string $notes = null): void
    {
        if (abs($quantity) > (float) $product->damaged_stock) {
            throw new \RuntimeException("Cannot restore more than the damaged stock on hand for {$product->name}.");
        }

        $product->damaged_stock = bcsub((string) $product->damaged_stock, (string) abs($quantity), 3);
        $product->save();

        $this->record($product, "damage_restore", abs($quantity), $product->cost_price, null, $notes);
    }

    public function decreaseFilamentForProduction(FilamentInventory $filament, float $grams, ?Model $reference, ?string $notes = null): void
    {
        $this->record($filament, "production_usage", -abs($grams), (float) $filament->costPerGram(), $reference, $notes);
    }

    public function increaseFilamentStock(FilamentInventory $filament, float $grams, ?float $unitCost, ?Model $reference, ?string $notes = null): void
    {
        $this->record($filament, "purchase", abs($grams), $unitCost, $reference, $notes);
    }
}