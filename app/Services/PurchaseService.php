<?php

namespace App\Services;

use App\Models\FilamentInventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @param array $data {
 *   supplier_id, purchased_at, payment_method, notes, amount_paid,
 *   items: [{ purchasable_type: product|filament, purchasable_id, description, quantity, unit_cost }]
 * }
 */
class PurchaseService
{
    public function __construct(
        private InventoryService $inventory,
        private AuditService $audit,
    ) {}

    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::create([
                "purchase_number" => $this->nextPurchaseNumber(),
                "supplier_id" => $data["supplier_id"],
                "purchased_at" => $data["purchased_at"] ?? now(),
                "payment_method" => $data["payment_method"] ?? "cash",
                "notes" => $data["notes"] ?? null,
                "created_by" => Auth::id(),
            ]);

            $total = "0.00";

            foreach ($data["items"] as $itemData) {
                $quantity = (string) $itemData["quantity"];
                $unitCost = (string) $itemData["unit_cost"];
                $lineTotal = bcmul($quantity, $unitCost, 2);
                $total = bcadd($total, $lineTotal, 2);

                $isFilament = $itemData["purchasable_type"] === "filament";
                $model = $isFilament
                    ? FilamentInventory::findOrFail($itemData["purchasable_id"])
                    : Product::findOrFail($itemData["purchasable_id"]);

                PurchaseItem::create([
                    "purchase_id" => $purchase->id,
                    "purchasable_type" => get_class($model),
                    "purchasable_id" => $model->id,
                    "description" => $itemData["description"] ?? $model->name,
                    "quantity" => $quantity,
                    "unit_cost" => $unitCost,
                    "line_total" => $lineTotal,
                ]);

                if ($isFilament) {
                    $this->inventory->increaseFilamentStock($model, (float) $quantity, (float) $unitCost, $purchase);
                } else {
                    $this->inventory->increaseProductStock($model, (float) $quantity, (float) $unitCost, $purchase);
                    // Keep a simple moving-average cost on the product for reporting defaults.
                    $model->cost_price = $unitCost;
                    $model->save();
                }
            }

            $purchase->total_cost = $total;
            $purchase->balance = $total;
            $purchase->save();

            if (!empty($data["amount_paid"]) && (float) $data["amount_paid"] > 0) {
                app(PaymentService::class)->recordPurchasePayment($purchase, (float) $data["amount_paid"], $data["payment_method"] ?? "cash");
            }

            $this->audit->log("purchase.created", $purchase, null, $purchase->toArray());

            return $purchase->fresh(["items", "supplier", "payments"]);
        });
    }

    private function nextPurchaseNumber(): string
    {
        $last = Purchase::withTrashed()->orderByDesc("id")->value("purchase_number");
        $lastNumber = $last ? (int) substr($last, strrpos($last, "-") + 1) : 0;

        return "PO-".str_pad((string) ($lastNumber + 1), 6, "0", STR_PAD_LEFT);
    }
}
