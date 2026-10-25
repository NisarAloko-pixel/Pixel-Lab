@php
    // Formats a quantity without trailing decimal noise: 1.000 -> "1", 1.500 -> "1.5".
    if (!function_exists('formatQty')) {
        function formatQty($value, int $decimals = 3): string
        {
            $formatted = number_format((float) $value, $decimals, '.', '');
            if (str_contains($formatted, '.')) {
                $formatted = rtrim(rtrim($formatted, '0'), '.');
            }
            return $formatted;
        }
    }

    // Filament rows (and prime_tower_grams) are saved as whole-plate/batch
    // quantities, same convention as print_hours — so every per-unit figure
    // on this page has to be divided by units per plate. Previously this
    // page displayed the raw plate-level numbers as if they were already
    // per-unit, which inflated the totals whenever units_per_plate was more
    // than 1.
    $unitsPerPlate = max(1, (int) ($product->units_per_plate ?? 1));
    $perUnitTotalGrams = $product->filaments->sum(fn ($f) => $f->grams_used + $f->waste_grams) / $unitsPerPlate;

    // The stored cost_price can be stale — it may have been saved before the
    // fix that divides whole-plate material cost by units per plate, so it
    // could still hold the un-divided, whole-plate total. Recompute the
    // per-unit cost live from the same figures driving the table below,
    // instead of trusting whatever is sitting in the cost_price column.
    $materialCost = 0;
    $weightedCostPerGram = 0;
    $gramsForAvg = 0;
    foreach ($product->filaments as $productFilament) {
        $costPerGram = $productFilament->filament ? (float) $productFilament->filament->costPerGram() : 0;
        $materialCost += ($productFilament->grams_used + $productFilament->waste_grams) * $costPerGram;
        $weightedCostPerGram += $costPerGram * $productFilament->grams_used;
        $gramsForAvg += $productFilament->grams_used;
    }
    $avgCostPerGram = $gramsForAvg > 0 ? $weightedCostPerGram / $gramsForAvg : 0;
    $materialCost += (float) ($product->prime_tower_grams ?? 0) * $avgCostPerGram;
    $materialCostPerUnit = $materialCost / $unitsPerPlate;

    $electricityCost = (float) ($product->print_hours ?? 0) * (float) ($product->electricity_rate_per_hour ?? 0);
    $liveCostPrice = round($materialCostPerUnit + $electricityCost, 2);
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">


        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to products
        </a>

        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="ledger-font text-3xl mb-1">{{ $product->name }}</h1>
                <p class="text-sm flex items-center gap-2" style="color:var(--ink-soft);">
                    SKU: {{ $product->sku }} &middot; {{ $product->category->name ?? 'No category' }}
                    <span class="badge" style="background:{{ $product->is_active ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $product->is_active ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>

            <a href="{{ route('products.edit', $product) }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                Edit Product
            </a>
        </div>

        {{-- Stat boxes --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <span class="block text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Current Stock</span>
                <span class="ledger-font text-2xl num">{{ formatQty($product->current_stock) }} pcs</span>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <span class="block text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Selling Price</span>
                <span class="ledger-font text-2xl num">{{ number_format($product->selling_price, 2) }}</span>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <span class="block text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Cost Price</span>
                <span class="ledger-font text-2xl num">{{ number_format($liveCostPrice, 2) }}</span>
                 <span class="block text-xs mt-1" style="color:var(--ink-soft);">{{ number_format($liveCostPrice, 2) }} per item</span>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <span class="block text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Total Cost Price (Available Stock)</span>
                <span class="ledger-font text-2xl num">{{ number_format($liveCostPrice * $product->current_stock, 2) }}</span>
               
            </div>
        </div>

        <div class="rounded-lg p-6 mb-6" style="background:var(--warn-soft); border:1px solid var(--line);">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="ledger-font text-lg" style="color:var(--warn);">Damaged Stock</h2>
                    <p class="text-sm mt-1" style="color:var(--ink-soft);">Units set aside as unsellable — kept separate from stock shown at sale time.</p>
                </div>
                <span class="ledger-font text-2xl num" style="color:var(--warn);">{{ formatQty($product->damaged_stock) }} {{ $product->unit }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('products.mark-damaged', $product) }}" class="rounded-md p-4" style="background:var(--surface); border:1px solid var(--line);">
                    @csrf
                    <p class="text-sm font-semibold mb-2">Mark units as damaged</p>
                    <div class="flex gap-2 mb-2">
                        <input type="number" step="0.001" min="0.001" max="{{ $product->current_stock }}" name="quantity" placeholder="Qty" required
                            class="field flex-1">
                    </div>
                    <input type="text" name="notes" placeholder="Reason (optional)" class="field w-full mb-2">
                    <button type="submit" class="text-sm font-semibold px-3 py-2 rounded-md w-full" style="background:var(--warn); color:#FFF;">
                        Mark damaged
                    </button>
                </form>

                <form method="POST" action="{{ route('products.restore-damaged', $product) }}" class="rounded-md p-4" style="background:var(--surface); border:1px solid var(--line);">
                    @csrf
                    <p class="text-sm font-semibold mb-2">Restore to sellable stock</p>
                    <div class="flex gap-2 mb-2">
                        <input type="number" step="0.001" min="0.001" max="{{ $product->damaged_stock }}" name="quantity" placeholder="Qty" required
                            class="field flex-1" @if(!$product->hasDamagedStock()) disabled @endif>
                    </div>
                    <input type="text" name="notes" placeholder="Reason (optional)" class="field w-full mb-2" @if(!$product->hasDamagedStock()) disabled @endif>
                    <button type="submit" class="text-sm font-semibold px-3 py-2 rounded-md w-full" style="background:var(--success); color:#FFF;" @if(!$product->hasDamagedStock()) disabled @endif>
                        Restore stock
                    </button>
                </form>
            </div>
        </div>

        {{-- Production Details --}}
        <div class="rounded-lg p-6 mb-6" style="background:var(--surface); border:1px solid var(--line);">
            <div class="flex justify-between items-start mb-4">
                <h2 class="ledger-font text-lg">Production Details</h2>
                <div class="text-sm text-right" style="color:var(--ink-soft);">
                    Print Hours: <span class="font-medium" style="color:var(--ink);">{{ $product->print_hours_formatted }}</span>
                </div>
            </div>

            <div class="rounded-md overflow-hidden mb-4" style="border:1px solid var(--line);">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Filament</th>
                            <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Used</th>
                            <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Waste</th>
                            <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Prime Tower</th>
                            <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Total (per unit)</th>
                            <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Total ({{ formatQty($product->current_stock) }} {{ $product->unit }} available)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->filaments as $productFilament)
                            @php
                                // grams_used / waste_grams were entered (and saved) as the
                                // whole plate's quantity — divide by units per plate to get
                                // the actual per-unit figures shown here.
                                $rowPerUnitUsed = $productFilament->grams_used / $unitsPerPlate;
                                $rowPerUnitWaste = $productFilament->waste_grams / $unitsPerPlate;
                                $rowPerUnitTotal = $rowPerUnitUsed + $rowPerUnitWaste;
                            @endphp
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-2">{{ $productFilament->filament->name ?? 'Unknown filament' }}</td>
                                <td class="px-4 py-2 text-right num">{{ formatQty($rowPerUnitUsed) }} g</td>
                                <td class="px-4 py-2 text-right num" style="color:var(--ink-soft);">{{ formatQty($rowPerUnitWaste) }} g</td>
                                <td class="px-4 py-2 text-right num" style="color:var(--ink-soft);">&mdash;</td>
                                <td class="px-4 py-2 text-right num font-medium">{{ formatQty($rowPerUnitTotal) }} g</td>
                                <td class="px-4 py-2 text-right num font-medium">{{ formatQty($rowPerUnitTotal * $product->current_stock) }} g</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-sm" style="color:var(--ink-soft);">No filaments assigned.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($product->filaments->isNotEmpty())
                        @php
                            // prime_tower_grams is also saved as a whole-plate quantity.
                            $primeTowerGrams = (float) ($product->prime_tower_grams ?? 0) / $unitsPerPlate;
                            $perUnitUsed = $product->filaments->sum('grams_used') / $unitsPerPlate;
                            $perUnitWaste = $product->filaments->sum('waste_grams') / $unitsPerPlate;
                            $perUnitTotalGramsWithPrimeTower = $perUnitTotalGrams + $primeTowerGrams;
                        @endphp
                        <tfoot>
                            <tr style="border-top:1px solid var(--line); background:var(--paper);">
                                <td class="px-4 py-2 font-semibold">Per unit (1 {{ $product->unit }})</td>
                                <td class="px-4 py-2 text-right num font-semibold">{{ formatQty($perUnitUsed) }} g</td>
                                <td class="px-4 py-2 text-right num font-semibold" style="color:var(--ink-soft);">{{ formatQty($perUnitWaste) }} g</td>
                                <td class="px-4 py-2 text-right num font-semibold">{{ formatQty($primeTowerGrams) }} g</td>
                                <td class="px-4 py-2 text-right num font-semibold">{{ formatQty($perUnitTotalGramsWithPrimeTower) }} g</td>
                                <td class="px-4 py-2 text-right num font-semibold">{{ formatQty($perUnitTotalGramsWithPrimeTower * $product->current_stock) }} g</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="text-sm" style="color:var(--ink-soft);">
                Minimum Stock Level: <span style="color:var(--ink);">{{ formatQty($product->min_stock_level ?? 0) }}</span>
            </div>
        </div>

        {{-- Damage / restore history --}}
        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <div class="flex justify-between items-start mb-4">
                <h2 class="ledger-font text-lg">Damage &amp; Restore History</h2>
                @if ($perUnitTotalGrams > 0)
                    <div class="text-sm text-right" style="color:var(--warn);">
                        Damaged stock — grams used: <span class="font-semibold">{{ formatQty($product->damaged_stock) }} {{ $product->unit }} &times; {{ formatQty($perUnitTotalGrams) }} g = {{ formatQty($product->damaged_stock * $perUnitTotalGrams) }} g</span>
                    </div>
                @endif
            </div>

            <div class="rounded-md overflow-hidden" style="border:1px solid var(--line);">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Date</th>
                            <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Action</th>
                            <th class="px-4 py-2 text-right font-medium" style="color:var(--ink-soft);">Quantity</th>
                            <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">Reason</th>
                            <th class="px-4 py-2 text-left font-medium" style="color:var(--ink-soft);">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($damageTransactions as $transaction)
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-2" style="color:var(--ink-soft);">{{ $transaction->created_at->timezone('Asia/Kabul')->format('M j, Y g:ia') }}</td>
                                <td class="px-4 py-2">
                                    @if ($transaction->type === 'damage')
                                        <span class="badge" style="background:var(--warn-soft); color:var(--warn);">Marked damaged</span>
                                    @else
                                        <span class="badge" style="background:var(--success-soft); color:var(--success);">Restored</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right num">{{ formatQty(abs($transaction->quantity)) }} {{ $product->unit }}</td>
                                <td class="px-4 py-2" style="color:var(--ink-soft);">{{ $transaction->notes ?: '—' }}</td>
                                <td class="px-4 py-2" style="color:var(--ink-soft);">{{ $transaction->createdBy->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center text-sm" style="color:var(--ink-soft);">No damage or restore activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</x-layouts.pixel>