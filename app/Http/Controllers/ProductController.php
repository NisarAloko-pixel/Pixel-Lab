<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\FilamentInventory;

class ProductController extends Controller
{
    const CATEGORIES = [
        'Keychains & Accessories',
        'Figurines & Miniatures',
        'Home & Decor',
        'Cosplay & Props',
        'Functional Parts',
        'Phone & Tech Accessories',
        'Toys & Games',
        'Jewelry',
        'Signage & Nameplates',
        'Custom / Commissioned',
        'Other',
    ];

    const ELECTRICITY_RATE_PER_HOUR = 15; // AF, per settings — fallback only, used when a product hasn't set its own rate

    public function index(Request $request)
    {
        $this->ensureCategoriesExist();

        $products = Product::with("category")
            ->when($request->filled("search"), fn ($q) => $q->where("name", "like", "%{$request->search}%")->orWhere("sku", "like", "%{$request->search}%"))
            ->when($request->filled("category_id"), fn ($q) => $q->where("category_id", $request->category_id))
            ->when($request->boolean("low_stock"), fn ($q) => $q->lowStock())
            ->latest("created_at")
            ->paginate(20)
            ->withQueryString();

        return view("products.index", [
            "products" => $products,
            "categories" => Category::orderBy("name")->get(),
            "displayTimezone" => \App\Models\Setting::get("timezone", config("app.timezone", "UTC")),
        ]);
    }

    public function create()
    {
        $this->ensureCategoriesExist();

        return view("products.create", [
            "categories" => Category::orderBy("name")->get(),
            "filaments" => FilamentInventory::orderBy("name")->get(),
        ]);
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            "name" => "required|string",
            "category_id" => "nullable|exists:categories,id",
            "description" => "nullable|string",
            "current_stock" => "nullable|numeric|min:0",
            "min_stock_level" => "nullable|numeric|min:0",
            "unit" => "required|string",
            "grams_used" => "nullable|numeric|min:0",
            "print_hours" => "nullable|numeric|min:0",
            "electricity_rate_per_hour" => "nullable|numeric|min:0",
            "units_per_plate" => "nullable|integer|min:1",
            "prime_tower_grams" => "nullable|numeric|min:0",
            "notes" => "nullable|string",
            "filaments" => "nullable|array",
            "filaments.*.filament_inventory_id" => "nullable|exists:filament_inventory,id",
            "filaments.*.grams_used" => "nullable|numeric|min:0",
            "filaments.*.waste_grams" => "nullable|numeric|min:0",
        ]);

        $data["sku"] = $this->nextSku();
        $data["selling_price"] = 0;

        $filamentRows = collect($data['filaments'] ?? [])
            ->filter(fn ($row) => !empty($row['filament_inventory_id']));

        $unitsPerPlate = max(1, (int) ($data['units_per_plate'] ?? 1));
        $primeTowerGrams = (float) ($data['prime_tower_grams'] ?? 0);
        $electricityRate = (float) ($data["electricity_rate_per_hour"] ?? self::ELECTRICITY_RATE_PER_HOUR);

        $data["cost_price"] = $this->calculateCostPrice(
            $filamentRows,
            $primeTowerGrams,
            (float) ($data['print_hours'] ?? 0),
            $electricityRate,
            $unitsPerPlate
        );

        // Persist the rate that was actually used for this cost calculation
        // (falls back to the class default above when none was submitted),
        // instead of discarding it — otherwise every edit screen shows the
        // fallback again since nothing was ever saved.
        $data["electricity_rate_per_hour"] = $electricityRate;

        // Opening stock physically consumes filament right away — the units
        // already exist and were already printed, so the material they used
        // has to come out of the spools now, not later.
        $openingStock = (float) ($data['current_stock'] ?? 0);

        unset($data['filaments']);

        try {
            $product = DB::transaction(function () use ($data, $filamentRows, $primeTowerGrams, $unitsPerPlate, $openingStock, $inventory) {
                $product = Product::create($data);

                foreach ($filamentRows as $row) {
                    $product->filaments()->create([
                        'filament_inventory_id' => $row['filament_inventory_id'],
                        'grams_used' => $row['grams_used'] ?? 0,
                        'waste_grams' => $row['waste_grams'] ?? 0,
                    ]);
                }

                $this->consumeFilamentForOpeningStock($product, $filamentRows, $primeTowerGrams, $unitsPerPlate, $openingStock, $inventory);

                return $product;
            });
        } catch (\RuntimeException $e) {
            // Thrown by InventoryService::record() when a filament doesn't have
            // enough weight_remaining_g to cover the opening stock entered —
            // the whole product creation rolls back with it (DB::transaction).
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route("products.index")->with("success", "Product {$product->name} created — cost price calculated at {$data['cost_price']}.");
    }

    public function update(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate([
            "name" => "required|string",
            "category_id" => "nullable|exists:categories,id",
            "description" => "nullable|string",
            "selling_price" => "required|numeric|min:0",
            "min_stock_level" => "nullable|numeric|min:0",
            "is_active" => "nullable|boolean",
            "notes" => "nullable|string",
            "unit" => "nullable|string",
            "grams_used" => "nullable|numeric|min:0",
            "print_hours" => "nullable|numeric|min:0",
            "electricity_rate_per_hour" => "nullable|numeric|min:0",
            "units_per_plate" => "nullable|integer|min:1",
            "prime_tower_grams" => "nullable|numeric|min:0",
            "filaments" => "nullable|array",
            "filaments.*.filament_inventory_id" => "nullable|exists:filament_inventory,id",
            "filaments.*.grams_used" => "nullable|numeric|min:0",
            "filaments.*.waste_grams" => "nullable|numeric|min:0",
        ]);
        // NOTE: current_stock is intentionally not editable here — it must only
        // change via InventoryService/PurchaseService so the ledger stays authoritative.
        //
        // Changing the filament recipe DOES move filament stock: the old recipe's
        // filament is returned to its spool and the new recipe's filament is deducted,
        // for every unit already produced (see Product::getUnitsProducedAttribute()).
        // If the recipe's real consumption didn't change, no stock moves.
        //
        // cost_price IS recalculated below from filament + print time inputs, same
        // formula as store(). That's distinct from letting someone type a cost_price
        // directly — it's still derived, just recomputed on edit.

        // Snapshot the OLD recipe before anything is changed.
        $oldRows  = $product->filaments()->get();
        $oldPrime = (float) ($product->prime_tower_grams ?? 0);
        $oldUpp   = max(1, (int) ($product->units_per_plate ?? 1));
        $units    = (float) $product->units_produced;

        $filamentRows = collect($data['filaments'] ?? [])
            ->filter(fn ($row) => !empty($row['filament_inventory_id']));

        // Fall back to the product's existing units_per_plate when the field wasn't
        // resubmitted, so cost still divides correctly rather than silently using 1.
        $unitsPerPlate = max(1, (int) ($data['units_per_plate'] ?? $product->units_per_plate ?? 1));
        $newPrime = (float) ($data['prime_tower_grams'] ?? $product->prime_tower_grams ?? 0);
        $electricityRate = (float) ($data["electricity_rate_per_hour"] ?? self::ELECTRICITY_RATE_PER_HOUR);

        $data["cost_price"] = $this->calculateCostPrice(
            $filamentRows,
            $newPrime,
            (float) ($data['print_hours'] ?? 0),
            $electricityRate,
            $unitsPerPlate
        );

        $data["electricity_rate_per_hour"] = $electricityRate;

        unset($data['filaments']);

        try {
            DB::transaction(function () use ($product, $data, $filamentRows, $oldRows, $oldPrime, $oldUpp, $units, $newPrime, $unitsPerPlate, $inventory) {
                $product->update($data);

                // Replace filament usage rows wholesale rather than diffing — same
                // append/recreate approach as store(), simplest to keep correct.
                $product->filaments()->get()->each->delete();
                foreach ($filamentRows as $row) {
                    $product->filaments()->create([
                        'filament_inventory_id' => $row['filament_inventory_id'],
                        'grams_used' => $row['grams_used'] ?? 0,
                        'waste_grams' => $row['waste_grams'] ?? 0,
                    ]);
                }

                if ($units <= 0) {
                    return; // nothing was ever made, so no filament to move
                }

                $old = $this->consumptionMap($oldRows, $oldPrime, $oldUpp, $units);
                $new = $this->consumptionMap($filamentRows, $newPrime, $unitsPerPlate, $units);

                if ($this->sameConsumption($old, $new)) {
                    return; // recipe didn't really change: leave stock alone
                }

                // 1) Give the old filaments their grams back (e.g. black).
                foreach ($old as $filamentId => $grams) {
                    $filament = FilamentInventory::find($filamentId);
                    if (!$filament) continue;

                    $restored = min(
                        (float) $filament->weight_purchased_g,
                        (float) $filament->weight_remaining_g + $grams
                    );
                    $filament->update(['weight_remaining_g' => $restored]);
                }

                // 2) Take the same units' worth from the new filaments (e.g. red).
                //    Throws RuntimeException if a spool doesn't have enough, which rolls
                //    back the whole edit, including the refund above.
                $this->consumeFilamentForOpeningStock(
                    $product, $filamentRows, $newPrime, $unitsPerPlate, $units, $inventory, 'Recipe change'
                );
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with("success", "Product updated — cost price recalculated at {$data['cost_price']}.");
    }

    public function show(Product $product)
    {
        $product->load('category', 'filament', 'filaments.filament');

        $damageTransactions = InventoryTransaction::where('trackable_type', Product::class)
            ->where('trackable_id', $product->id)
            ->whereIn('type', ['damage', 'damage_restore'])
            ->with('createdBy')
            ->latest()
            ->get();

        return view('products.show', compact('product', 'damageTransactions'));
    }

    public function edit(Product $product)
    {
        $this->ensureCategoriesExist();

        $product->load('filaments.filament');

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'filaments' => FilamentInventory::orderBy('name')->get(),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete(); // soft delete — never permanently removed, stays in sale history

        return redirect()->route('products.index')->with('success', "Product {$product->name} deleted.");
    }

    /**
     * Adds newly printed units to sellable stock. Optionally deducts the filament
     * those units consumed (same per-unit formula as opening stock). Everything
     * runs in one transaction, so a spool without enough filament rolls it all back.
     */
    public function restock(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
            'deduct_filament' => 'nullable|boolean',
        ]);

        $qty = (float) $data['quantity'];

        try {
            DB::transaction(function () use ($request, $product, $inventory, $qty, $data) {
                if ($request->boolean('deduct_filament')) {
                    $this->consumeFilamentForOpeningStock(
                        $product,
                        $product->filaments,
                        (float) ($product->prime_tower_grams ?? 0),
                        max(1, (int) ($product->units_per_plate ?? 1)),
                        $qty,
                        $inventory,
                        'Restock'
                    );
                }

                $inventory->increaseProductStock(
                    $product,
                    $qty,
                    (float) $product->cost_price,
                    null,
                    $data['notes'] ?? 'Restock'
                );
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Restocked {$qty} {$product->unit} of {$product->name}.");
    }

    /** Moves units from sellable stock into damaged_stock, logged via InventoryService. */
    public function markDamaged(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate([
            "quantity" => "required|numeric|min:0.001|max:{$product->current_stock}",
            "notes" => "nullable|string",
        ]);

        $inventory->markDamaged($product, (float) $data["quantity"], $data["notes"] ?? null);

        return back()->with("success", "Marked {$data['quantity']} {$product->unit} as damaged.");
    }

    /** Moves units back from damaged_stock into sellable stock. */
    public function restoreDamaged(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate([
            "quantity" => "required|numeric|min:0.001|max:{$product->damaged_stock}",
            "notes" => "nullable|string",
        ]);

        $inventory->restoreDamaged($product, (float) $data["quantity"], $data["notes"] ?? null);

        return back()->with("success", "Restored {$data['quantity']} {$product->unit} to sellable stock.");
    }

    /**
     * Computes a product's per-unit cost price from its filament usage, prime tower
     * waste, and print time — matching the "live" formula used on the product show
     * page (products.show), so the stored cost_price column never drifts from what
     * that page displays.
     *
     * $filamentRows is expected as rows of ['filament_inventory_id', 'grams_used', 'waste_grams'].
     * grams_used/waste_grams/prime tower grams/print_hours are all whole-plate (batch)
     * quantities — the resulting material cost is divided by $unitsPerPlate to get a
     * true per-unit figure. Electricity cost is intentionally NOT divided by
     * units-per-plate, matching the existing live calculation on the show page.
     */
    private function calculateCostPrice(
        \Illuminate\Support\Collection $filamentRows,
        float $primeTowerGrams,
        float $printHours,
        float $electricityRate,
        int $unitsPerPlate
    ): float {
        $materialCost = 0;
        $weightedCostPerGram = 0;
        $gramsForAvg = 0;

        foreach ($filamentRows as $row) {
            $filament = FilamentInventory::find($row['filament_inventory_id']);
            if (!$filament) continue;

            $grams = (float) ($row['grams_used'] ?? 0);
            $waste = (float) ($row['waste_grams'] ?? 0);
            $costPerGram = (float) $filament->costPerGram();

            $materialCost += ($grams + $waste) * $costPerGram;

            // Weighted average cost/gram (by grams_used only, not waste) is used to
            // price prime-tower waste, which isn't tied to any single filament row.
            $weightedCostPerGram += $costPerGram * $grams;
            $gramsForAvg += $grams;
        }

        $avgCostPerGram = $gramsForAvg > 0 ? $weightedCostPerGram / $gramsForAvg : 0;
        $materialCost += $primeTowerGrams * $avgCostPerGram;

        $materialCostPerUnit = $materialCost / $unitsPerPlate;
        $electricityCost = $printHours * $electricityRate;

        return round($materialCostPerUnit + $electricityCost, 2);
    }

    /**
     * Grams consumed per filament spool for $units finished units of a recipe:
     * [filament_inventory_id => grams]. Uses the same per-unit formula as
     * consumeFilamentForOpeningStock(), so a refund always matches what was deducted.
     */
    private function consumptionMap(
        \Illuminate\Support\Collection $rows,
        float $primeTowerGrams,
        int $unitsPerPlate,
        float $units
    ): array {
        $rows = $rows->filter(fn ($r) => !empty($r['filament_inventory_id']));
        if ($rows->isEmpty()) {
            return [];
        }

        $totalGrams = $rows->sum(fn ($r) => (float) ($r['grams_used'] ?? 0));
        $map = [];

        foreach ($rows as $row) {
            $grams = (float) ($row['grams_used'] ?? 0);
            $waste = (float) ($row['waste_grams'] ?? 0);

            $primeShare = $totalGrams > 0
                ? $primeTowerGrams * ($grams / $totalGrams)
                : $primeTowerGrams / $rows->count();

            $id = $row['filament_inventory_id'];
            $map[$id] = ($map[$id] ?? 0) + (($grams + $waste + $primeShare) / $unitsPerPlate) * $units;
        }

        return $map;
    }

    /** True when two consumption maps use the same spools in (almost) the same amounts. */
    private function sameConsumption(array $a, array $b): bool
    {
        ksort($a);
        ksort($b);

        if (array_keys($a) !== array_keys($b)) {
            return false;
        }

        foreach ($a as $id => $grams) {
            if (abs($grams - $b[$id]) > 0.01) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deducts real filament weight for newly produced units (opening stock at
     * product creation, or a later restock). Each filament row's own
     * (grams_used + waste_grams) is divided by units per plate to get its true
     * per-unit consumption; prime tower waste is split across rows proportionally
     * to their grams_used share, since prime tower purge isn't tied to any single
     * filament on its own.
     *
     * $label only changes the wording of the ledger note ("Opening stock" / "Restock").
     */
    private function consumeFilamentForOpeningStock(
        Product $product,
        \Illuminate\Support\Collection $filamentRows,
        float $primeTowerGrams,
        int $unitsPerPlate,
        float $openingStock,
        InventoryService $inventory,
        string $label = 'Opening stock'
    ): void {
        if ($openingStock <= 0 || $filamentRows->isEmpty()) {
            return;
        }

        $totalGramsUsedAcrossRows = $filamentRows->sum(fn ($row) => (float) ($row['grams_used'] ?? 0));

        foreach ($filamentRows as $row) {
            $filament = FilamentInventory::find($row['filament_inventory_id']);
            if (!$filament) continue;

            $grams = (float) ($row['grams_used'] ?? 0);
            $waste = (float) ($row['waste_grams'] ?? 0);

            $primeTowerShare = $totalGramsUsedAcrossRows > 0
                ? $primeTowerGrams * ($grams / $totalGramsUsedAcrossRows)
                : $primeTowerGrams / $filamentRows->count();

            $perUnitGrams = ($grams + $waste + $primeTowerShare) / $unitsPerPlate;
            $totalConsumed = $perUnitGrams * $openingStock;

            if ($totalConsumed <= 0) continue;

            $inventory->decreaseFilamentForProduction(
                $filament,
                $totalConsumed,
                $product,
                "{$label} — {$product->name} ({$openingStock} {$product->unit})"
            );
        }
    }

    private function nextSku(): string
    {
        $last = Product::withTrashed()->where('sku', 'like', 'PRD-%')->orderByDesc('id')->value('sku');
        $lastNumber = $last ? (int) substr($last, 4) : 0;

        return 'PRD-'.str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
    }

    /** Creates the fixed category list once; safe to call repeatedly since it skips existing names. */
    private function ensureCategoriesExist(): void
    {
        foreach (self::CATEGORIES as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}