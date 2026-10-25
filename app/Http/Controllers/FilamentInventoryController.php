<?php

namespace App\Http\Controllers;

use App\Models\FilamentInventory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
class FilamentInventoryController extends Controller
{
    const TYPES = [
    'PLA', 'PLA+', 'PLA Matte', 'PLA Silk', 'PLA Silk Dual Color', 'PLA Silk Tri-Color',
    'PLA Rainbow', 'PLA Glow', 'PLA Wood', 'PLA Marble', 'PLA Carbon Fiber',
    'PETG', 'PETG-CF', 'ABS', 'ABS-CF', 'ASA', 'ASA-CF',
    'TPU-95A', 'TPE-90A', 'TPE-85A', 'PA6', 'PA6-CF',
    'Resin', 'Other',
];

   public function create(Request $request)
{
    $purchase = null;

    if ($request->filled('purchase_id')) {
        $purchase = Purchase::where('item_type', 'filament')
            ->where('status', 'arrived')
            
->where(fn ($q) => $q->where('stock_applied', false)->orWhereNull('stock_applied'))
->findOrFail($request->purchase_id);
    }

    return view('filament.create', [
        'suppliers' => Supplier::orderBy('name')->get(),
        'types' => self::TYPES,
        'purchase' => $purchase,
        'baseCurrency' => Setting::get('currency', 'USD'),
    ]);
}

public function edit(FilamentInventory $filament)
{
    return view('filament.edit', [
        'filament' => $filament,
        'suppliers' => Supplier::orderBy('name')->get(),
        'types' => self::TYPES,
        'baseCurrency' => Setting::get('currency', 'USD'),

    ]);
}

public function update(Request $request, FilamentInventory $filament)
{
    $data = $request->validate([
        'type' => 'required|in:'.implode(',', self::TYPES),
        'color' => 'required|string',
        'brand' => 'nullable|string',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'purchase_date' => 'nullable|date',
        'weight_purchased_g' => 'required|numeric|min:0.01',
        'weight_remaining_g' => 'required|numeric|min:0|lte:weight_purchased_g',
        'purchase_price' => 'required|numeric|min:0',
        'shipping_cost' => 'nullable|numeric|min:0',
    ]);

    $data['shipping_cost'] = $data['shipping_cost'] ?? 0;
    $data['name'] = trim(($data['brand'] ?? '').' '.$data['type'].' '.$data['color']);

    // Only recalculate cost/kg if weight, price or shipping actually changed.
    // Otherwise a remaining-only correction would wipe a weighted-average cost from restocking.
    $costInputsChanged =
        bccomp((string) $data['weight_purchased_g'], (string) $filament->weight_purchased_g, 2) !== 0
        || bccomp((string) $data['purchase_price'], (string) $filament->purchase_price, 2) !== 0
        || bccomp((string) $data['shipping_cost'], (string) $filament->shipping_cost, 2) !== 0;

    if ($costInputsChanged) {
        $landedCost = bcadd((string) $data['purchase_price'], (string) $data['shipping_cost'], 2);
        $kg = bcdiv((string) $data['weight_purchased_g'], '1000', 6);
        $data['cost_per_kg'] = bcdiv($landedCost, $kg, 2);
    }

    $filament->update($data);

    return redirect()->route('filament.index')->with('success', 'Filament spool updated.');
}

public function destroy(FilamentInventory $filament)
{
    $filament->delete(); // soft delete — stays in printing-job cost history

    return redirect()->route('filament.index')->with('success', 'Filament spool deleted.');
}
  public function index(Request $request)
{
    $filaments = FilamentInventory::with('supplier')
        ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
        ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
        ->when($request->boolean('low_stock'), function ($q) {
    $q->where(function ($q) {
        $q->where('weight_remaining_g', '<=', 50)
            ->orWhere(function ($q) {
                $q->where('weight_purchased_g', '>', 0)
                    ->whereRaw('weight_remaining_g / weight_purchased_g <= 0.15');
            });
    });
})
        ->latest('purchase_date')
        ->paginate(20)
        ->withQueryString();

    $stockSummary = FilamentInventory::selectRaw('
            type, color, brand,
            SUM(weight_remaining_g) as total_remaining,
            SUM(weight_purchased_g) as total_purchased
        ')
        ->groupBy('type', 'color', 'brand')
        ->orderBy('type')
        ->orderBy('color')
        ->get();

   $pendingPurchases = Purchase::where('item_type', 'filament')
    ->where('status', 'arrived')
    ->where(fn ($q) => $q->where('stock_applied', false)->orWhereNull('stock_applied'))
    ->with('supplier')
        ->orderByDesc('arrived_at')
        ->get();

    return view('filament.index', [
        'filaments' => $filaments,
        'suppliers' => Supplier::orderBy('name')->get(),
        'types' => self::TYPES,
        'stockSummary' => $stockSummary,
        'pendingPurchases' => $pendingPurchases,
        'baseCurrency' => Setting::get('currency', 'USD'),
    ]);
}

    public function store(Request $request)
{
    $data = $request->validate([
        'type' => 'required|in:'.implode(',', self::TYPES),
        'color' => 'required|string',
        'brand' => 'nullable|string',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'weight_purchased_g' => 'required|numeric|min:0.01',
        'purchase_price' => 'required|numeric|min:0',
        'shipping_cost' => 'nullable|numeric|min:0',
        'purchase_date' => 'nullable|date',
        'purchase_id' => 'nullable|exists:purchases,id',
    ]);

    $purchaseId = $data['purchase_id'] ?? null;
    unset($data['purchase_id']);

    $data['shipping_cost'] = $data['shipping_cost'] ?? 0;
    $data['name'] = trim(($data['brand'] ?? '').' '.$data['type'].' '.$data['color']);
    $data['weight_remaining_g'] = $data['weight_purchased_g'];

    $landedCost = bcadd((string) $data['purchase_price'], (string) $data['shipping_cost'], 2);
    $kg = bcdiv((string) $data['weight_purchased_g'], '1000', 6);
    $data['cost_per_kg'] = (float) $kg > 0 ? bcdiv($landedCost, $kg, 2) : 0;

    DB::transaction(function () use ($data, $purchaseId) {
        FilamentInventory::create($data);

        if ($purchaseId) {
            $purchase = Purchase::where('item_type', 'filament')
                ->where('status', 'arrived')
                ->where(fn ($q) => $q->where('stock_applied', false)->orWhereNull('stock_applied'))
                ->find($purchaseId);

            // Model update (not query-builder) so audit log and sync see the change
            $purchase?->update([
                'stock_applied'    => true,
                'stock_applied_at' => now(),
            ]);
        }
    });

    return redirect()->route('filament.index')->with('success', 'Filament spool added.');
}
}