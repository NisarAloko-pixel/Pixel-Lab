<?php
// Place in app/Http/Controllers/PurchaseController.php

namespace App\Http\Controllers;

use App\Models\FilamentInventory;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Supply;
use Illuminate\Http\Request;
use App\Support\Currencies;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Support\PurchasePayments;

class PurchaseController extends Controller
{
    const LOCATIONS = ['Lab', 'Home', 'Other'];

    public function index(Request $request)
    {
        $purchases = Purchase::with(['supply', 'supplier'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('item_type', $request->type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->search);
                $id = ltrim($s, '#');

                $q->where(function ($q) use ($s, $id) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('filament_brand', 'like', "%{$s}%")
                      ->orWhere('filament_type', 'like', "%{$s}%")
                      ->orWhere('filament_color', 'like', "%{$s}%")
                      ->orWhereHas('supply', fn ($q) => $q->where('name', 'like', "%{$s}%"))
                      ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$s}%"));

                    if (ctype_digit($id)) {
                        $q->orWhere('id', (int) $id);
                    }
                });
            })
            ->orderByDesc('created_at')   // newest created first
            ->orderByDesc('id')           // tie-breaker for rows created in the same second
            ->paginate(20)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
            'locations' => self::LOCATIONS,
            'baseCurrency' => Setting::get('currency', 'USD'),
        ]);
    }

    public function create()
    {
        return view('purchases.create', [
            'supplies' => Supply::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'supplyCategories' => SupplyController::CATEGORIES,
            'supplyUnits' => SupplyController::UNITS,
            'filamentTypes' => FilamentInventoryController::TYPES,
            'currencies' => Currencies::LIST,
            'baseCurrency' => Setting::get('currency', 'USD'),
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $baseCurrency = Setting::get('currency', 'USD');

        $order = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'account_id' => 'nullable|exists:accounts,id',
            'order_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'shipping_cost' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:supply,filament',
            'items.*.supply_id' => 'nullable|exists:supplies,id',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.category' => 'nullable|in:'.implode(',', SupplyController::CATEGORIES),
            'items.*.unit' => 'nullable|in:'.implode(',', SupplyController::UNITS),
            'items.*.filament_type' => 'nullable|in:'.implode(',', FilamentInventoryController::TYPES),
            'items.*.filament_color' => 'nullable|string|max:255',
            'items.*.filament_brand' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        // Per-item conditional requirements.
        foreach ($order['items'] as $i => $item) {
            if ($item['item_type'] === 'supply' && empty($item['supply_id'])) {
                $request->validate([
                    "items.$i.name" => 'required|string|max:255',
                    "items.$i.unit" => 'required|in:'.implode(',', SupplyController::UNITS),
                ]);
            }
            if ($item['item_type'] === 'filament') {
                $request->validate([
                    "items.$i.filament_type" => 'required|in:'.implode(',', FilamentInventoryController::TYPES),
                    "items.$i.filament_color" => 'required|string|max:255',
                ]);
            }
        }

        if ($order['currency'] === $baseCurrency) {
            $order['exchange_rate'] = 1;
        }

        $shippingTotal = (float) ($order['shipping_cost'] ?? 0);
        $valueSum = array_sum(array_column($order['items'], 'total_price'));
        $itemCount = count($order['items']);
        $batchId = $itemCount > 1 ? (string) Str::uuid() : null;

        // "Paying now" is entered in the order's currency. Blank = pay in full.
        $orderTotal = $valueSum + $shippingTotal;
        $paidOrder = isset($order['amount_paid'])
            ? min((float) $order['amount_paid'], $orderTotal)
            : $orderTotal;
        $paidRatio = $orderTotal > 0 ? $paidOrder / $orderTotal : 1;

        // An unpaid balance has to be owed to somebody.
        if ($paidRatio < 1 && empty($order['supplier_id'])) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Choose a supplier to record an unpaid balance.',
            ]);
        }

        // All items and their account deductions succeed or fail together.
        DB::transaction(function () use ($order, $shippingTotal, $valueSum, $itemCount, $batchId, $paidRatio) {
            foreach ($order['items'] as $item) {
                // Shipping split proportional to each item's own price — a $50 spool
                // carries more of the shipping bill than a $5 pack of screws.
                $shareRatio = $valueSum > 0 ? ($item['total_price'] / $valueSum) : (1 / $itemCount);
                $itemShipping = round($shippingTotal * $shareRatio, 2);

                $quantity = (float) $item['quantity'];
                $pricePerUnit = $quantity > 0 ? round($item['total_price'] / $quantity, 4) : 0;

                // This item's full cost (price + shipping share) in base currency.
                $costBase = bcmul(
                    bcadd((string) $item['total_price'], (string) $itemShipping, 2),
                    (string) $order['exchange_rate'],
                    2
                );

                $purchase = Purchase::create([
                    'batch_id' => $batchId,
                    'item_type' => $item['item_type'],
                    'supply_id' => $item['supply_id'] ?? null,
                    'name' => $item['name'] ?? null,
                    'category' => $item['category'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'filament_type' => $item['filament_type'] ?? null,
                    'filament_color' => $item['filament_color'] ?? null,
                    'filament_brand' => $item['filament_brand'] ?? null,
                    'supplier_id' => $order['supplier_id'] ?? null,
                    'quantity' => $quantity,
                    'total_price' => $item['total_price'],
                    'price_per_unit' => $pricePerUnit,
                    'shipping_cost' => $itemShipping,
                    'currency' => $order['currency'],
                    'exchange_rate' => $order['exchange_rate'],
                    // The payment is spread across items in proportion to their cost.
                    'amount_paid' => round((float) $costBase * $paidRatio, 2),
                    'order_date' => $order['order_date'] ?? null,
                    'notes' => $order['notes'] ?? null,
                    'status' => 'in_transit',
                ]);

                // Only the amount actually paid leaves the account.
                Transaction::syncPurchasePayment($purchase, (string) $purchase->amount_paid, $order['account_id'] ?? null);
            }
        });

        $message = $itemCount > 1 ? "{$itemCount} items logged." : 'Purchase logged.';

        return redirect()->route('purchases.index')->with('success', $message);
    }

    /** Details page for a single purchase (linked from the transactions page). */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supply', 'supplier']);

        return view('purchases.show', [
            'purchase' => $purchase,
            'baseCurrency' => Setting::get('currency', 'USD'),
        ]);
    }

    public function edit(Purchase $purchase)
    {
        if ($purchase->isArrived()) {
            return redirect()->route('purchases.index')->with('error', 'Arrived purchases can\'t be edited.');
        }

        return view('purchases.edit', [
            'purchase' => $purchase,
            'supplies' => Supply::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'supplyCategories' => SupplyController::CATEGORIES,
            'supplyUnits' => SupplyController::UNITS,
            'filamentTypes' => FilamentInventoryController::TYPES,
            'currencies' => Currencies::LIST,
            'baseCurrency' => Setting::get('currency', 'USD'),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->isArrived()) {
            return redirect()->route('purchases.index')->with('error', 'Arrived purchases can\'t be edited.');
        }

        $baseCurrency = Setting::get('currency', 'USD');

        $data = $request->validate([
            'item_type' => 'required|in:supply,filament',
            'supply_id' => 'nullable|exists:supplies,id',
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|in:'.implode(',', SupplyController::CATEGORIES),
            'unit' => 'nullable|in:'.implode(',', SupplyController::UNITS),
            'filament_type' => 'nullable|in:'.implode(',', FilamentInventoryController::TYPES),
            'filament_color' => 'nullable|string|max:255',
            'filament_brand' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0.01',
            'total_price' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'order_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'price_per_unit' => 'nullable|numeric|min:0',
        ]);

        if ($data['item_type'] === 'supply' && empty($data['supply_id'])) {
            $request->validate([
                'name' => 'required|string|max:255',
                'unit' => 'required|in:'.implode(',', SupplyController::UNITS),
            ]);
        }

        if ($data['item_type'] === 'filament') {
            $request->validate([
                'filament_type' => 'required|in:'.implode(',', FilamentInventoryController::TYPES),
                'filament_color' => 'required|string|max:255',
            ]);
        }

        if ($data['currency'] === $baseCurrency) {
            $data['exchange_rate'] = 1;
        }

        $data['shipping_cost'] = $data['shipping_cost'] ?? 0;

        DB::transaction(function () use ($purchase, $data) {
            // A purchase that was paid in full stays fully paid if its price is edited.
            $wasFullyPaid = bccomp((string) $purchase->amount_paid, $purchase->total_cost_base, 2) >= 0;

            $purchase->update($data);

            $cost = $purchase->total_cost_base;
            if ($wasFullyPaid || bccomp((string) $purchase->amount_paid, $cost, 2) > 0) {
                $purchase->update(['amount_paid' => $cost]);
            }

            Transaction::syncPurchasePayment($purchase, (string) $purchase->amount_paid);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase updated.');
    }

    public function destroy(Purchase $purchase)
    {
        // Once stock has landed on a Supply/Filament record, inventory numbers
        // trace back to this purchase, so it can't be removed. A purchase that is
        // merely "arrived" but not yet applied to stock is still safe to delete.
        if ($purchase->stock_applied) {
            return redirect()->route('purchases.index')->with('error', 'This purchase has already updated stock and can\'t be deleted.');
        }

        $snapshot = [
            'purchase_id' => $purchase->id,
            'item' => $purchase->displayName(),
            'status' => $purchase->status,
            'user_id' => auth()->id(),
        ];

        DB::transaction(function () use ($purchase) {
            // A cancelled purchase already had its payment returned.
            if (! $purchase->isCancelled()) {
                Transaction::syncPurchasePayment($purchase, 0); // money goes back
            }

            $purchase->delete();
        });

        Log::info('Purchase deleted', $snapshot);

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted.');
    }

    /**
     * Pay some or all of the remaining due on a purchase (amounts in base currency).
     */
    public function pay(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($purchase->isCancelled() || bccomp($purchase->amount_due_base, '0', 2) <= 0) {
            return back()->with('error', 'Nothing left to pay on this purchase.');
        }

        $applied = DB::transaction(fn () => PurchasePayments::apply(
            $purchase,
            (string) $data['amount'],
            $data['account_id'],
            $data['payment_date'] ?? null,
            $data['notes'] ?? null
        ));

        return back()->with('success', 'Payment of '.number_format((float) $applied, 2).' recorded.');
    }

    /**
     * The moment a purchase physically shows up. This is where quantity and
     * cost actually land on the real Supply/Filament record — nothing before
     * this point touches stock.
     */
    public function arrive(Request $request, Purchase $purchase)
    {
        if ($purchase->isArrived()) {
            return redirect()->route('purchases.index')->with('success', 'Already marked as arrived.');
        }

        $data = $request->validate([
            'arrived_location' => 'required|in:'.implode(',', self::LOCATIONS),
        ]);

        $purchase->update([
            'status' => 'arrived',
            'arrived_at' => now(),
            'arrived_location' => $data['arrived_location'],
        ]);

        return redirect()->route('purchases.index')->with('success', 'Marked as arrived — stock updated.');
    }
}