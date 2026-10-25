<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('filament.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to filament inventory</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Edit Filament Spool</h1>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <form method="POST" action="{{ route('filament.update', $filament) }}" class="grid grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Type</label>
                    <select name="type" required class="field w-full">
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(old('type', $filament->type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Color</label>
                    <input name="color" value="{{ old('color', $filament->color) }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Brand (optional)</label>
                    <input name="brand" value="{{ old('brand', $filament->brand) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supplier</label>
                    <select name="supplier_id" class="field w-full">
                        <option value="">No supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $filament->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($filament->purchase_date)->format('Y-m-d')) }}" class="field w-full">
                </div>

               <div>
    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Weight Purchased (g)</label>
    <input type="number" step="0.01" min="0.01" name="weight_purchased_g"
        value="{{ old('weight_purchased_g', $filament->weight_purchased_g) }}" required class="field w-full">
</div>
<div>
    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Weight Remaining (g)</label>
    <input type="number" step="0.01" min="0" name="weight_remaining_g"
        value="{{ old('weight_remaining_g', $filament->weight_remaining_g) }}" required class="field w-full">
</div>
<div>
    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Total Purchase Price ({{ $baseCurrency }})</label>
    <input type="number" step="0.01" min="0" name="purchase_price"
        value="{{ old('purchase_price', $filament->purchase_price) }}" required class="field w-full">
</div>
<div>
    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Shipping Cost ({{ $baseCurrency }})</label>
    <input type="number" step="0.01" min="0" name="shipping_cost"
        value="{{ old('shipping_cost', $filament->shipping_cost) }}" class="field w-full">
</div>

<div class="col-span-2 rounded-lg px-4 py-3 text-sm" style="background:var(--paper); border:1px solid var(--line);">
    <div><span style="color:var(--ink-soft);">Current Cost/kg (landed):</span> {{ number_format($filament->cost_per_kg, 2) }}</div>
    <p class="text-xs mt-1" style="color:var(--ink-soft);">
        Recalculated automatically if you change weight purchased, price or shipping.
        Changing only the remaining weight leaves it as is.
    </p>
</div>

                <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>
