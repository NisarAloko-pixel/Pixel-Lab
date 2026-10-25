<?php
// Replace app/Http/Controllers/SupplyController.php with this file.
// (Rebuilt: check CATEGORIES / UNITS below and the view + route names in the notes.)

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Supply;
use Illuminate\Http\Request;
  use Illuminate\Support\Facades\DB;
  use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SupplyController extends Controller
{
    // Everything currently in your supplies table MUST be listed here,
    // otherwise purchases of those items fail validation.
    // Add any extra categories/units you used to have.
    const CATEGORIES = ['Keyring', 'Bulb', 'Glue', 'Tools', 'Electronics'];

    const UNITS = ['pcs', 'm', 'cm', 'mm', 'kg', 'g', 'l', 'ml', 'pack', 'roll', 'set', 'box'];

    public function index(Request $request)
    {
        $supplies = Supply::with('supplier')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->boolean('low_stock'), fn ($q) => $q->where('quantity_remaining', '<', 5))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Supply purchases that have arrived but haven't been added to stock yet.
        $pendingPurchases = Purchase::with(['supply', 'supplier'])
            ->where('item_type', 'supply')
            ->where('status', 'arrived')
            ->where(fn ($q) => $q->where('stock_applied', false)->orWhereNull('stock_applied'))
            ->orderBy('arrived_at')
            ->get();

        return view('supplies.index', [
            'supplies' => $supplies,
            'pendingPurchases' => $pendingPurchases,
            'categories' => self::CATEGORIES,
            'units' => self::UNITS,
        ]);
    }

    public function create(Request $request)
{
    $purchase = null;
    if ($request->filled('purchase_id')) {
        $purchase = Purchase::where('item_type', 'supply')
            ->where('status', 'arrived')
            ->find($request->purchase_id);
    }

    return view('supplies.create', [
        'purchase'     => $purchase,
        'baseCurrency' => config('app.base_currency', 'USD'), // use however you read it elsewhere
        'suppliers'    => Supplier::orderBy('name')->get(),
        'categories'   => self::CATEGORIES,
        'units'        => self::UNITS,
    ]);
}

public function store(Request $request)
{
    $data = $this->validated($request);

    $supply = DB::transaction(function () use ($request, $data) {
        $supply = new Supply($data);
        $supply->quantity_remaining = $data['quantity_purchased'];
        $supply->recalculateCostPerUnit();
        $supply->save();

        if ($request->filled('purchase_id')) {
            $purchase = Purchase::where('item_type', 'supply')
                ->where('status', 'arrived')
                ->where(fn ($q) => $q->where('stock_applied', false)->orWhereNull('stock_applied'))
                ->find($request->purchase_id);

            $purchase?->update([
                'stock_applied'    => true,
                'stock_applied_at' => now(),
                'supply_id'        => $supply->id,
            ]);
        }

        return $supply;
    });

    return redirect()->route('supplies.index')->with('success', "{$supply->name} added.");
}

   public function show(Supply $supply)
{
    $supply->load('supplier');

    $damageTransactions = $supply->inventoryTransactions()
        ->whereIn('type', ['damage', 'restore'])
        ->with('createdBy')
        ->latest()
        ->get();

    return view('supplies.show', compact('supply', 'damageTransactions'));
}

public function markDamaged(Request $request, Supply $supply)
{
    $data = $request->validate([
        'quantity' => 'required|numeric|min:0.01',
        'notes'    => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($supply, $data) {
        $supply = Supply::lockForUpdate()->findOrFail($supply->id);

        if ((float) $data['quantity'] > (float) $supply->quantity_remaining) {
            throw ValidationException::withMessages([
                'quantity' => 'Cannot mark more than the remaining stock as damaged.',
            ]);
        }

        $supply->quantity_remaining = (float) $supply->quantity_remaining - (float) $data['quantity'];
        $supply->damaged_stock      = (float) $supply->damaged_stock + (float) $data['quantity'];
        $supply->save();

        $supply->inventoryTransactions()->create([
            'type'       => 'damage',
            'quantity'   => -abs($data['quantity']),
            'notes'      => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);
    });

    return back()->with('success', 'Marked as damaged.');
}

public function restoreDamaged(Request $request, Supply $supply)
{
    $data = $request->validate([
        'quantity' => 'required|numeric|min:0.01',
        'notes'    => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($supply, $data) {
        $supply = Supply::lockForUpdate()->findOrFail($supply->id);

        if ((float) $data['quantity'] > (float) $supply->damaged_stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Cannot restore more than the damaged stock.',
            ]);
        }

        $supply->damaged_stock      = (float) $supply->damaged_stock - (float) $data['quantity'];
        $supply->quantity_remaining = (float) $supply->quantity_remaining + (float) $data['quantity'];
        $supply->save();

        $supply->inventoryTransactions()->create([
            'type'       => 'restore',
            'quantity'   => abs($data['quantity']),
            'notes'      => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);
    });

    return back()->with('success', 'Restored to stock.');
}
    public function edit(Supply $supply)
    {
        return view('supplies.edit', [
            'supply' => $supply,
            'suppliers' => Supplier::orderBy('name')->get(),
            'categories' => self::CATEGORIES,
            'units' => self::UNITS,
        ]);
    }

    public function update(Request $request, Supply $supply)
    {
        $data = $this->validated($request);

        // If the purchased quantity changed, move the remaining stock by the same amount.
        $diff = (float) $data['quantity_purchased'] - (float) $supply->quantity_purchased;

        $supply->fill($data);
        $supply->quantity_remaining = max(0, (float) $supply->quantity_remaining + $diff);
        $supply->recalculateCostPerUnit();
        $supply->save();

        return redirect()->route('supplies.index')->with('success', "{$supply->name} updated.");
    }

    public function destroy(Supply $supply)
    {
        $supply->delete();

        return redirect()->route('supplies.index')->with('success', "{$supply->name} deleted.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|in:'.implode(',', self::CATEGORIES),
            'brand' => 'nullable|string|max:255',
            'unit' => 'required|in:'.implode(',', self::UNITS),
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity_purchased' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }
}