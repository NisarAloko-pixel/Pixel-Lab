<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PrintingJob;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supply;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates, edits, cancels, and deletes sales atomically: invoice number, line totals,
 * cost snapshot, inventory movements and customer balance are all written in a single
 * DB transaction so a failure partway through can never leave inventory or balances
 * inconsistent with the invoice. This is the ONLY place that should create, edit,
 * cancel, or delete a Sale.
 *
 * @param array $data {
 *   customer_id, sold_at, discount, tax, payment_method, notes, amount_paid,
 *   items: [{ product_id?, printing_job_id?, description, quantity, unit_price, discount?, is_free?,
 *             extras?: [{ name?, cost?, supply_id?, quantity? }] }]
 * }
 *
 * Note on "extras": an extra's quantity and cost are TOTALS for that whole line item
 * (e.g. "3100 pcs of ring, $20 total"), not a per-unit rate that gets multiplied by
 * the item's own quantity. This matches how the sale form collects them.
 *
 * Note on "is_free": a free line still deducts stock and counts its cost (so it shows as a
 * loss in gross profit), but its unit price, discount and line total are all 0.
 */
class SaleService
{
    public function __construct(
        private InventoryService $inventory,
        private AuditService $audit,
    ) {}

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = new Sale([
                "invoice_number" => $this->nextInvoiceNumber(),
                "customer_id" => $data["customer_id"] ?? null,
                "sold_at" => $data["sold_at"] ?? now(),
                "discount" => $data["discount"] ?? 0,
                "tax" => $data["tax"] ?? 0,
                "payment_method" => $data["payment_method"] ?? "cash",
                "notes" => $data["notes"] ?? null,
                "status" => "completed",
                "created_by" => Auth::id(),
            ]);
            $sale->save();

            [$subtotal, $costTotal] = $this->writeItems($sale, $data["items"]);

            $sale->subtotal = $subtotal;
            $sale->total = bcadd(bcsub($subtotal, (string) $sale->discount, 2), (string) $sale->tax, 2);
            $sale->cost_total = $costTotal;
            $sale->save();

            if (!empty($data["amount_paid"]) && (float) $data["amount_paid"] > 0) {
                $payment = app(PaymentService::class)->recordSalePayment($sale, (float) $data["amount_paid"], $data["payment_method"] ?? "cash", $data["notes"] ?? null);
                Transaction::recordSalePayment($payment, $sale);
            } else {
                $this->refreshBalance($sale);
            }

            $this->audit->log("sale.created", $sale, null, $sale->toArray());

            return $sale->fresh(["items", "customer", "payments"]);
        });
    }

    /**
     * Rebuilds a sale's line items and header fields. Payments are intentionally left
     * untouched here — recording/adjusting a payment is a separate action from
     * correcting an invoice's items, so editing only recalculates balance/status
     * against the new total via refreshBalance().
     */
    public function update(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            $old = $sale->toArray();

            // Reverse inventory (products and any supply extras) for the items being
            // replaced, the same way cancel() does, so stock stays accurate across the edit.
            $this->reverseInventoryForItems($sale, "Edited {$sale->invoice_number}");

            // Delete each line through its model, NOT with $sale->items()->delete().
            // A bulk delete fires no model events, so the Firestore sync never hears about it
            // and the old lines come back as duplicates on every other machine.
            $sale->items()->get()->each(fn ($item) => $item->delete());

            $sale->fill([
                "customer_id" => $data["customer_id"] ?? null,
                "sold_at" => $data["sold_at"] ?? $sale->sold_at,
                "discount" => $data["discount"] ?? 0,
                "tax" => $data["tax"] ?? 0,
                "payment_method" => $data["payment_method"] ?? $sale->payment_method,
                "notes" => $data["notes"] ?? null,
            ]);

            [$subtotal, $costTotal] = $this->writeItems($sale, $data["items"]);

            $sale->subtotal = $subtotal;
            $sale->total = bcadd(bcsub($subtotal, (string) $sale->discount, 2), (string) $sale->tax, 2);
            $sale->cost_total = $costTotal;
            $sale->save();

            $this->refreshBalance($sale);

            $this->audit->log("sale.updated", $sale, $old, $sale->fresh()->toArray());

            return $sale->fresh(["items", "customer", "payments"]);
        });
    }

    public function cancel(Sale $sale, ?string $reason = null): Sale
    {
        return DB::transaction(function () use ($sale, $reason) {
            $old = $sale->toArray();

            // Reverse inventory (products and any supply extras) for every line, using an
            // explicit "return" movement rather than deleting the original "sale" row.
            $this->reverseInventoryForItems($sale, "Cancelled {$sale->invoice_number}");

            Transaction::reverseSalePayments($sale);
            $sale->status = "cancelled";
            $sale->cancellation_reason = $reason;
            $sale->save(); // soft-deletes are separate; a cancelled sale still exists and reports can choose to exclude it

            $this->audit->log("sale.cancelled", $sale, $old, $sale->toArray());

            return $sale;
        });
    }

    /**
     * Soft-deletes a sale. Reverses inventory first, unless the sale was already
     * cancelled — in that case stock was already returned by cancel() and reversing
     * it again would double-count it. Payments are preserved for audit history.
     */
    public function delete(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $old = $sale->toArray();

            if ($sale->status !== "cancelled") {
                $this->reverseInventoryForItems($sale, "Deleted {$sale->invoice_number}");
            }
            Transaction::reverseSalePayments($sale);
            $sale->delete();

            $this->audit->log("sale.deleted", $sale, $old, null);
        });
    }

    public function refreshBalance(Sale $sale): void
    {
        $paid = (string) $sale->payments()->sum("amount");
        $sale->amount_paid = $paid;
        $sale->balance = bcsub((string) $sale->total, $paid, 2);
        $sale->payment_status = match (true) {
            (float) $sale->balance <= 0 => "paid",
            (float) $paid > 0 => "partial",
            default => "unpaid",
        };
        $sale->save();
    }

    /**
     * Reverses inventory for every existing item on a sale: product stock (as before),
     * plus stock for any "extra" that was linked to a real Supply record. Used by
     * update(), cancel(), and delete() so the three don't drift out of sync.
     */
    private function reverseInventoryForItems(Sale $sale, string $reason): void
    {
        foreach ($sale->items as $item) {
            if ($item->product_id && $item->product) {
                $this->inventory->record($item->product, "return", (float) $item->quantity, $item->unit_cost, $sale, $reason);
            }

            foreach ($item->extra_items ?? [] as $extra) {
                if (empty($extra["supply_id"])) {
                    continue;
                }

                $supply = Supply::find($extra["supply_id"]);
                if (! $supply) {
                    continue; // supply may have since been deleted; nothing to reverse against
                }

                // extra["quantity"] is already the TOTAL consumed for this line — not
                // a per-item rate — so it's used as-is, not multiplied by item quantity.
                $consumedQty = (string) ($extra["quantity"] ?? 1);
                $unitCost = $extra["supply_unit_cost"] ?? $supply->cost_per_unit;

                $this->inventory->record($supply, "return", (float) $consumedQty, (float) $unitCost, $sale, $reason);
            }
        }
    }

    /**
     * Builds SaleItem rows for a sale (used by both create() and update()), decrements
     * product stock for each product line, and decrements supply stock for any "extra"
     * linked to a real Supply record. Returns [subtotal, costTotal].
     */
    private function writeItems(Sale $sale, array $itemsData): array
    {
        $subtotal = "0.00";
        $costTotal = "0.00";

        foreach ($itemsData as $itemData) {
            $product = isset($itemData["product_id"]) ? Product::findOrFail($itemData["product_id"]) : null;
            $job = isset($itemData["printing_job_id"]) ? PrintingJob::findOrFail($itemData["printing_job_id"]) : null;

            $quantity = (string) $itemData["quantity"];

            // Free items: no revenue, but stock and cost are still consumed like a normal sale.
            $isFree = filter_var($itemData["is_free"] ?? false, FILTER_VALIDATE_BOOLEAN);
            $unitPrice = $isFree ? "0" : (string) ($itemData["unit_price"] ?? 0); // set manually at sale time
            $lineDiscount = $isFree ? "0" : (string) ($itemData["discount"] ?? 0);
            $unitCost = (string) ($product->cost_price ?? ($job ? bcdiv((string) $job->total_cost, $quantity, 2) : 0));

            $extras = array_values(array_filter(
                $itemData["extras"] ?? [],
                fn ($e) => !empty($e["name"]) || !empty($e["cost"]) || !empty($e["supply_id"])
            ));

            // Deduct stock for any extra tied to a real Supply. extra["quantity"] is the
            // TOTAL amount of that supply consumed for this whole line — not a per-item
            // rate — so it is used as-is, never multiplied by the item's own quantity.
            foreach ($extras as &$extra) {
                if (empty($extra["supply_id"])) {
                    continue;
                }

                $supply = Supply::findOrFail($extra["supply_id"]);
                $consumedQty = (string) ($extra["quantity"] ?? 1);

                $this->inventory->decreaseSupplyStockForSale($supply, (float) $consumedQty, $sale, "Sale {$sale->invoice_number}");

                $extra["supply_unit_cost"] = (string) $supply->cost_per_unit;
            }
            unset($extra);

            // extra["cost"] is a COST for profit tracking — it's already priced into
            // unit_price, so it is NOT added into line_total (revenue). It still counts
            // toward costTotal below.
            $extraCost = array_reduce($extras, fn ($carry, $e) => bcadd($carry, (string) ($e["cost"] ?? 0), 2), "0.00");

            $lineTotal = bcsub(bcmul($quantity, $unitPrice, 2), $lineDiscount, 2);

            $row = [
                "sale_id" => $sale->id,
                "product_id" => $product?->id,
                "printing_job_id" => $job?->id,
                "description" => $itemData["description"] ?? ($product->name ?? $job->name),
                "quantity" => $quantity,
                "unit_price" => $unitPrice,
                "unit_cost" => $unitCost,
                "extra_cost" => $extraCost,
                "extra_items" => $extras ?: null,
                "discount" => $lineDiscount,
                "line_total" => $lineTotal,
            ];

            // Only written when the column exists, so a machine that hasn't run the
            // is_free migration yet can still save sales instead of failing.
            if ($this->hasFreeColumn()) {
                $row["is_free"] = $isFree;
            }

            SaleItem::create($row);

            $subtotal = bcadd($subtotal, $lineTotal, 2);
            $costTotal = bcadd($costTotal, bcadd(bcmul($quantity, $unitCost, 2), $extraCost, 2), 2);

            if ($product) {
                $this->inventory->decreaseProductStockForSale($product, (float) $quantity, $sale, "Sale {$sale->invoice_number}");
            }
        }

        return [$subtotal, $costTotal];
    }

    private function hasFreeColumn(): bool
    {
        static $has = null;

        return $has ??= Schema::hasColumn('sale_items', 'is_free');
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = Setting::get("invoice_prefix", "PS-");
        $last = Sale::withTrashed()->where("invoice_number", "like", "{$prefix}%")->orderByDesc("id")->value("invoice_number");
        $lastNumber = $last ? (int) substr($last, strlen($prefix)) : 0;

        return $prefix.str_pad((string) ($lastNumber + 1), 6, "0", STR_PAD_LEFT);
    }
}