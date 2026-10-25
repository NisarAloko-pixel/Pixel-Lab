@php($appCurrency = \App\Models\Setting::get('currency', 'AF'))
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('products.show', $product) }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to {{ $product->name }}</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Edit Product</h1>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <form method="POST" action="{{ route('products.update', $product) }}" class="grid grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Product Name</label>
                    <input name="name" value="{{ old('name', $product->name) }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                    <select name="category_id" class="field w-full">
                        <option value="">No category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Unit</label>
                    <input name="unit" placeholder="pcs, g, ..." value="{{ old('unit', $product->unit) }}" required class="field w-full">
                </div>

                {{-- Filaments used --}}
                <div class="col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-medium" style="color:var(--ink-soft);">Filaments Used</label>
                        <button type="button" id="add_filament_btn" class="text-xs font-medium px-3 py-1.5 rounded-md" style="background:var(--paper); border:1px solid var(--line); color:var(--accent);">
                            + Add filament
                        </button>
                    </div>

                    {{-- Tells the user that changing the recipe moves stock between spools --}}
                    @if ((float) ($product->units_produced ?? 0) > 0)
                        <div class="rounded-md px-3 py-2 mb-3 text-xs" style="background:var(--warn-soft); color:var(--warn);">
                            {{ rtrim(rtrim(number_format((float) $product->units_produced, 3), '0'), '.') }} {{ $product->unit }} of this product were already made.
                            If you change a filament or its grams, the old filament is returned to its spool and the new
                            filament is deducted for those {{ $product->unit }}.
                        </div>
                    @endif

                    <div class="mb-3" style="max-width:220px;">
                        <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Units per plate/batch</label>
                        <input type="number" min="1" step="1" name="units_per_plate" id="units_per_plate" value="{{ old('units_per_plate', $product->units_per_plate ?? 1) }}" class="field w-full">
                        <p class="text-[11px] mt-1" style="color:var(--ink-soft);">
                            If this plate prints several identical pieces at once (e.g. 50 keychains), enter how many. Leave as 1 for a single printed model.
                        </p>
                    </div>

                    <div id="filaments_container" class="space-y-3"></div>

                    <div id="filament_totals" class="flex flex-wrap gap-x-6 gap-y-1 mt-3 text-xs" style="color:var(--ink-soft);">
                        <span>Plate total grams: <strong id="plate_total_grams" style="color:var(--ink);">0</strong> g</span>
                        <span>Plate total waste: <strong id="plate_total_waste" style="color:var(--ink);">0</strong> g</span>
                    </div>
                    <div id="filament_per_unit_totals" class="flex flex-wrap gap-x-6 gap-y-1 mt-1 text-xs font-medium" style="color:var(--accent);">
                        <span>Per unit grams: <strong id="total_grams_used">0</strong> g</span>
                        <span>Per unit waste: <strong id="total_waste_grams">0</strong> g</span>
                    </div>

                    <div id="prime_tower_row" class="hidden mt-3 rounded-lg p-3" style="background:var(--paper); border:1px dashed var(--line);">
                        <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Prime Tower Waste (g) â€” extra purge when switching between filaments</label>
                        <input type="number" step="0.01" min="0" name="prime_tower_grams" id="prime_tower_grams" value="{{ old('prime_tower_grams', $product->prime_tower_grams ?? 0) }}" class="field w-full sm:w-48">
                    </div>

                    <p class="text-xs mt-2" style="color:var(--ink-soft);">Leave empty for a non-printed item.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Minimum Stock Level</label>
                    <input type="number" step="0.001" name="min_stock_level" value="{{ old('min_stock_level', rtrim(rtrim(number_format((float) ($product->min_stock_level ?? 0), 3, '.', ''), '0'), '.')) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Selling Price</label>
                    <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required class="field w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Grams Used (per unit)</label>
                    <input type="number" step="0.01" name="grams_used" id="grams_used_input" value="{{ old('grams_used', $product->grams_used) }}" class="field w-full" readonly style="background:var(--field-disabled, var(--paper)); color:var(--ink-soft);">
                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Auto-calculated from filaments above</p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Print Time (per plate/batch)</label>
                    @php
                        $unitsPerPlateForEdit = max(1, (int) ($product->units_per_plate ?? 1));
                        $platePrintHours = ($product->print_hours ?? 0) * $unitsPerPlateForEdit;
                    @endphp
                    <div class="flex gap-2">
                        <input type="number" min="0" id="print_hours_h" placeholder="Hours" value="{{ old('print_hours_h', floor($platePrintHours)) }}" class="field w-full">
                        <input type="number" min="0" max="59" id="print_hours_m" placeholder="Minutes" value="{{ old('print_hours_m', round(($platePrintHours - floor($platePrintHours)) * 60)) }}" class="field w-full">
                    </div>
                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Total time for the whole plate â€” divided by units per plate above to get the per-unit time and cost.</p>
                    <input type="hidden" name="print_hours" id="print_hours_total" value="{{ old('print_hours', $product->print_hours ?? 0) }}">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Electricity Rate (per hour)</label>
                    <input type="number" step="0.01" min="0" name="electricity_rate_per_hour" id="electricity_rate_input" value="{{ old('electricity_rate_per_hour', $product->electricity_rate_per_hour ?? 0) }}" class="field w-full">
                </div>

                <div class="col-span-2 rounded-lg px-4 py-3 flex justify-between items-center" style="background:var(--paper); border:1px solid var(--line);">
                    <span class="text-sm" style="color:var(--ink-soft);">Estimated Cost Price (recalculated on save)</span>
                    <span id="cost_preview" class="text-lg font-semibold" style="color:var(--ink);">0.00 {{ $appCurrency }}</span>
                </div>

                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Description</label>
                    <textarea name="description" rows="3" class="field w-full">{{ old('description', $product->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Status</label>
                    <select name="is_active" class="field w-full">
                        <option value="1" @selected(old('is_active', $product->is_active) == 1)>Active</option>
                        <option value="0" @selected(old('is_active', $product->is_active) == 0)>Inactive</option>
                    </select>
                </div>

                <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<template id="filament_options_template">
    <option value="">Select filament</option>
    @foreach ($filaments as $filament)
        <option value="{{ $filament->id }}" data-cost-per-kg="{{ $filament->cost_per_kg }}" data-remaining-g="{{ $filament->weight_remaining_g }}" data-purchased-g="{{ $filament->weight_purchased_g }}">
            {{ $filament->name }} &mdash; {{ number_format($filament->weight_remaining_g, 1) }} g left &mdash; {{ number_format($filament->cost_per_kg / 1000, 4) }} {{ $appCurrency }}/g
        </option>
    @endforeach
</template>

<template id="filament_row_template">
    <div class="rounded-lg p-4 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end" style="border:1px solid var(--line); background:var(--paper);">
        <div class="sm:col-span-5">
            <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Filament / Color</label>
            <select class="filament-row-select field w-full"></select>
            <p class="filament-stock-hint hidden text-[11px] mt-1" style="color:var(--ink-soft);"></p>
            <p class="filament-stock-warning hidden text-[11px] mt-1 font-medium" style="color:var(--danger);"></p>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Grams used</label>
            <input type="number" step="0.01" min="0" class="filament-grams-input field w-full" value="0">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Purge/waste (g)</label>
            <input type="number" step="0.01" min="0" class="filament-waste-input field w-full" value="0">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Cost</label>
            <input type="text" class="filament-row-cost field w-full" disabled value="0.00 {{ $appCurrency }}" style="background:var(--field-disabled, var(--paper)); color:var(--ink-soft);">
        </div>
        <div class="sm:col-span-1 flex justify-end">
            <button type="button" class="remove-filament-btn text-xs font-medium" style="color:var(--danger);">Remove</button>
        </div>
    </div>
</template>

{{--
    Existing filament rows for this product, serialized so the JS can rebuild
    the same rows the create form builds, pre-filled with saved values.
    (After a failed submit this holds the user's typed values, via old().)
--}}
<script id="existing_filaments_data" type="application/json">
    {!! json_encode(
        old('filaments', $product->filaments->map(fn ($usage) => [
            'filament_inventory_id' => $usage->filament_inventory_id,
            'grams_used' => $usage->grams_used,
            'waste_grams' => $usage->waste_grams,
        ])->values())
    ) !!}
</script>

{{--
    The recipe exactly as it is SAVED in the database (never old() input). The stock
    check below compares the edited recipe against this one, because the saved recipe's
    grams were already taken from the spools and will be returned when you save.
--}}
<script id="saved_recipe_data" type="application/json">
    {!! json_encode([
        'units_produced' => (float) ($product->units_produced ?? 0),
        'units_per_plate' => max(1, (int) ($product->units_per_plate ?? 1)),
        'prime_tower_grams' => (float) ($product->prime_tower_grams ?? 0),
        'rows' => $product->filaments->map(fn ($usage) => [
            'id' => (string) $usage->filament_inventory_id,
            'grams' => (float) $usage->grams_used,
            'waste' => (float) $usage->waste_grams,
        ])->values(),
    ]) !!}
</script>

<script>
    let filamentRowIndex = 0;

    const SAVED_RECIPE = JSON.parse(document.getElementById('saved_recipe_data').textContent || '{}');

    // Strips trailing zeros from a decimal value so "5.000" displays as "5"
    // and "12.250" displays as "12.25", instead of always showing a fixed
    // number of decimal places (grams and stock columns are stored to 3
    // decimal places, which otherwise pads every whole number with .000).
    function trimDecimal(value, maxDecimals = 3) {
        const num = parseFloat(value || 0);
        if (Number.isNaN(num)) return '0';
        return parseFloat(num.toFixed(maxDecimals)).toString();
    }

    function filamentOptionsHtml() {
        return document.getElementById('filament_options_template').innerHTML;
    }

    function addFilamentRow(existing = null, options = {}) {
        const index = filamentRowIndex++;
        const container = document.getElementById('filaments_container');
        const fragment = document.getElementById('filament_row_template').content.cloneNode(true);
        const row = fragment.querySelector('div');

        const select = row.querySelector('.filament-row-select');
        select.innerHTML = filamentOptionsHtml();
        select.name = `filaments[${index}][filament_inventory_id]`;

        const gramsInput = row.querySelector('.filament-grams-input');
        const wasteInput = row.querySelector('.filament-waste-input');
        gramsInput.name = `filaments[${index}][grams_used]`;
        wasteInput.name = `filaments[${index}][waste_grams]`;

        if (existing) {
            if (existing.filament_inventory_id) select.value = existing.filament_inventory_id;
            gramsInput.value = trimDecimal(existing.grams_used ?? 0);
            wasteInput.value = trimDecimal(existing.waste_grams ?? 0);
        }

        container.appendChild(row);
        wireFilamentRowEvents(row);
        if (!options.skipRecalculate) {
            recalculateCostPreview();
        }
    }

    function wireFilamentRowEvents(row) {
        row.querySelector('.filament-row-select').addEventListener('change', recalculateCostPreview);
        row.querySelector('.filament-grams-input').addEventListener('input', recalculateCostPreview);
        row.querySelector('.filament-waste-input').addEventListener('input', recalculateCostPreview);
        row.querySelector('.remove-filament-btn').addEventListener('click', () => {
            row.remove();
            recalculateCostPreview();
        });
    }

    function recalculateFilamentRow(row) {
        const select = row.querySelector('.filament-row-select');
        const selectedOption = select.options[select.selectedIndex];
        const costPerKg = parseFloat(selectedOption?.dataset.costPerKg || 0);
        const costPerGram = costPerKg / 1000;

        const grams = parseFloat(row.querySelector('.filament-grams-input').value || 0);
        const waste = parseFloat(row.querySelector('.filament-waste-input').value || 0);
        const rowCost = (grams + waste) * costPerGram;

        row.querySelector('.filament-row-cost').value = rowCost.toFixed(2) + ' ' + @json($appCurrency);
        // Remaining grams + price per gram under the dropdown
        const hint = row.querySelector('.filament-stock-hint');
        if (selectedOption && selectedOption.value) {
            const remaining = parseFloat(selectedOption.dataset.remainingG || 0);
            hint.textContent = `${remaining.toFixed(1)} g left \u00B7 ${costPerGram.toFixed(4)} ${@json($appCurrency)}/g`;
            hint.classList.remove('hidden');
        } else {
            hint.classList.add('hidden');
        }

        return rowCost;
    }

    // Grams consumed per spool for `units` finished units of a recipe: { filamentId: grams }.
    // Mirrors ProductController::consumptionMap() on the server.
    function consumptionMap(rows, primeTowerGrams, unitsPerPlate, units) {
        rows = rows.filter(r => r.id);
        const map = {};
        if (!rows.length) return map;

        const totalGrams = rows.reduce((sum, r) => sum + r.grams, 0);
        rows.forEach(r => {
            const primeShare = totalGrams > 0
                ? primeTowerGrams * (r.grams / totalGrams)
                : primeTowerGrams / rows.length;
            map[r.id] = (map[r.id] || 0) + ((r.grams + r.waste + primeShare) / unitsPerPlate) * units;
        });
        return map;
    }

    function sameConsumption(a, b) {
        const keys = new Set([...Object.keys(a), ...Object.keys(b)]);
        for (const k of keys) {
            if (Math.abs((a[k] || 0) - (b[k] || 0)) > 0.01) return false;
        }
        return true;
    }

    // Warns only when saving would actually move stock AND a spool can't cover it.
    // Needed grams = (edited recipe) x (units already produced).
    // Available grams = what is left on the spool + what the saved recipe will return to it.
    function validateFilamentStock() {
        const rows = document.querySelectorAll('#filaments_container > div');
        const units = SAVED_RECIPE.units_produced || 0;

        const formRows = Array.from(rows).map(row => ({
            id: row.querySelector('.filament-row-select').value,
            grams: parseFloat(row.querySelector('.filament-grams-input').value || 0),
            waste: parseFloat(row.querySelector('.filament-waste-input').value || 0),
        }));

        const unitsPerPlate = Math.max(1, parseFloat(document.getElementById('units_per_plate').value || 1));
        const primeTowerGrams = rows.length > 1
            ? parseFloat(document.getElementById('prime_tower_grams').value || 0)
            : 0;

        const oldMap = consumptionMap(SAVED_RECIPE.rows || [], SAVED_RECIPE.prime_tower_grams || 0, SAVED_RECIPE.units_per_plate || 1, units);
        const newMap = consumptionMap(formRows, primeTowerGrams, unitsPerPlate, units);
        const recipeChanged = units > 0 && !sameConsumption(oldMap, newMap);

        rows.forEach(row => {
            const select = row.querySelector('.filament-row-select');
            const opt = select.options[select.selectedIndex];
            const warning = row.querySelector('.filament-stock-warning');
            const gramsInput = row.querySelector('.filament-grams-input');
            const wasteInput = row.querySelector('.filament-waste-input');

            let over = false;

            if (recipeChanged && opt && opt.value) {
                const remaining = parseFloat(opt.dataset.remainingG || 0);
                const purchased = parseFloat(opt.dataset.purchasedG || remaining);
                const available = Math.min(purchased, remaining + (oldMap[opt.value] || 0));
                const needed = newMap[opt.value] || 0;

                if (needed > available + 0.01) {
                    over = true;
                    warning.textContent = `\u26A0 This recipe needs ${needed.toFixed(1)} g for the ${trimDecimal(units)} already made, but only ${available.toFixed(1)} g is available, short by ${(needed - available).toFixed(1)} g.`;
                }
            }

            warning.classList.toggle('hidden', !over);
            gramsInput.style.borderColor = over ? 'var(--danger)' : '';
            wasteInput.style.borderColor = over ? 'var(--danger)' : '';
        });
    }

    function recalculateFilamentTotals() {
        const rows = document.querySelectorAll('#filaments_container > div');
        let plateGrams = 0;
        let plateWaste = 0;

        rows.forEach(row => {
            plateGrams += parseFloat(row.querySelector('.filament-grams-input').value || 0);
            plateWaste += parseFloat(row.querySelector('.filament-waste-input').value || 0);
        });

        const primeTowerRow = document.getElementById('prime_tower_row');
        const primeTowerInput = document.getElementById('prime_tower_grams');

        if (rows.length > 1) {
            primeTowerRow.classList.remove('hidden');
        } else {
            primeTowerRow.classList.add('hidden');
            primeTowerInput.value = 0;
        }

        const primeTowerGrams = parseFloat(primeTowerInput.value || 0);
        plateWaste += primeTowerGrams;

        const unitsPerPlate = Math.max(1, parseFloat(document.getElementById('units_per_plate').value || 1));
        const perUnitGrams = plateGrams / unitsPerPlate;
        const perUnitWaste = plateWaste / unitsPerPlate;

        document.getElementById('plate_total_grams').textContent = trimDecimal(plateGrams);
        document.getElementById('plate_total_waste').textContent = trimDecimal(plateWaste);
        document.getElementById('total_grams_used').textContent = trimDecimal(perUnitGrams);
        document.getElementById('total_waste_grams').textContent = trimDecimal(perUnitWaste);
        document.getElementById('grams_used_input').value = trimDecimal(perUnitGrams);

        return { plateGrams, plateWaste, primeTowerGrams, unitsPerPlate, perUnitGrams, perUnitWaste };
    }

    function updatePrintHoursTotal() {
        const hours = parseFloat(document.getElementById('print_hours_h').value || 0);
        const minutes = parseFloat(document.getElementById('print_hours_m').value || 0);
        const unitsPerPlate = Math.max(1, parseFloat(document.getElementById('units_per_plate').value || 1));
        const totalPlateHours = hours + minutes / 60;
        // Entered as time for the whole plate/batch, same as grams â€” store the
        // per-unit figure, since that's what print_hours represents everywhere
        // else in the app (product detail page, cost history, etc).
        document.getElementById('print_hours_total').value = (totalPlateHours / unitsPerPlate).toFixed(4);
    }

    function recalculateCostPreview() {
        const hoursInput = document.getElementById('print_hours_h');
        const minutesInput = document.getElementById('print_hours_m');
        const electricityRateInput = document.getElementById('electricity_rate_input');
        const preview = document.getElementById('cost_preview');

        let plateMaterialCost = 0;
        let weightedCostPerGram = 0;
        let gramsForAvg = 0;

        document.querySelectorAll('#filaments_container > div').forEach(row => {
            plateMaterialCost += recalculateFilamentRow(row);

            const select = row.querySelector('.filament-row-select');
            const selectedOption = select.options[select.selectedIndex];
            const costPerKg = parseFloat(selectedOption?.dataset.costPerKg || 0);
            const costPerGram = costPerKg / 1000;
            const grams = parseFloat(row.querySelector('.filament-grams-input').value || 0);

            weightedCostPerGram += costPerGram * grams;
            gramsForAvg += grams;
        });

        const { primeTowerGrams, unitsPerPlate } = recalculateFilamentTotals();
        const avgCostPerGram = gramsForAvg > 0 ? (weightedCostPerGram / gramsForAvg) : 0;
        plateMaterialCost += primeTowerGrams * avgCostPerGram;

        const perUnitMaterialCost = plateMaterialCost / unitsPerPlate;

        // Print time is also entered for the whole plate/batch (same as grams),
        // so it gets divided by units per plate too, before applying the rate.
        const hours = parseFloat(hoursInput.value || 0);
        const minutes = parseFloat(minutesInput.value || 0);
        const totalPlateHours = hours + (minutes / 60);
        const perUnitHours = totalPlateHours / unitsPerPlate;
        const electricityRate = parseFloat(electricityRateInput.value || 0);
        const electricityCost = perUnitHours * electricityRate;

        preview.textContent = (perUnitMaterialCost + electricityCost).toFixed(2) + ' ' + @json($appCurrency);

        validateFilamentStock();
    }

    document.getElementById('add_filament_btn').addEventListener('click', () => addFilamentRow());
    document.getElementById('print_hours_h').addEventListener('input', () => { updatePrintHoursTotal(); recalculateCostPreview(); });
    document.getElementById('print_hours_m').addEventListener('input', () => { updatePrintHoursTotal(); recalculateCostPreview(); });
    document.getElementById('electricity_rate_input').addEventListener('input', recalculateCostPreview);
    document.getElementById('prime_tower_grams').addEventListener('input', recalculateCostPreview);
    document.getElementById('units_per_plate').addEventListener('input', () => { updatePrintHoursTotal(); recalculateCostPreview(); });

    // Rebuild rows from existing data (or old() input after a failed submit); fall back to one empty row.
    // skipRecalculate: true so an intermediate 1-row state doesn't trip the
    // "only 1 filament -> hide & zero prime tower" branch in
    // recalculateFilamentTotals() before the remaining rows are added â€” that
    // was wiping out a correctly-saved prime tower value on every page load.
    const existingFilaments = JSON.parse(document.getElementById('existing_filaments_data').textContent || '[]');
    if (existingFilaments.length > 0) {
        existingFilaments.forEach(existing => addFilamentRow(existing, { skipRecalculate: true }));
    } else {
        addFilamentRow();
    }

    updatePrintHoursTotal();
    recalculateCostPreview();
</script>
</x-layouts.pixel>