@php
    // Per-unit price of the arrived purchase, converted to base currency
    $prefillUnitPrice = null;
    if ($purchase) {
        $perUnit = (float) ($purchase->price_per_unit
            ?: ($purchase->quantity > 0 ? $purchase->total_price / $purchase->quantity : 0));
        $prefillUnitPrice = round($perUnit * (float) $purchase->exchange_rate, 4);
    }
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('supplies.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to supplies</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Add Supply</h1>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if ($purchase)
            <div class="rounded-lg px-4 py-3 mb-6 text-sm" style="background:var(--accent-soft); color:var(--accent-ink); border:1px solid var(--line);">
                Applying arrived purchase #{{ $purchase->id }} â€” fields below are prefilled from it.
            </div>
        @endif

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <form method="POST" action="{{ route('supplies.store') }}" class="grid grid-cols-2 gap-4"
                x-data="{
                    qty: @js((float) old('quantity_purchased', $purchase->quantity ?? 0)),
                    price: @js((float) old('purchase_price', $prefillUnitPrice ?? 0)),
                    shipping: @js((float) old('shipping_cost', $purchase ? round((float) $purchase->shipping_cost * (float) $purchase->exchange_rate, 2) : 0)),
                    get itemsTotal() { return (parseFloat(this.qty) || 0) * (parseFloat(this.price) || 0); },
                    get grandTotal() { return this.itemsTotal + (parseFloat(this.shipping) || 0); },
                    fmt(n) { return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                }">
                @csrf

                @if ($purchase)
                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                @endif

                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                    <input name="name" value="{{ old('name', $purchase->name ?? '') }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                    <select name="category" class="field w-full">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', $purchase->category ?? null) === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Unit</label>
                    <select name="unit" required class="field w-full">
                        <option value="" disabled @selected(!old('unit', $purchase->unit ?? null))>Select unit&hellip;</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit }}" @selected(old('unit', $purchase->unit ?? null) === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Brand (optional)</label>
                    <input name="brand" value="{{ old('brand') }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supplier</label>
                    <select name="supplier_id" class="field w-full">
                        <option value="">No supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id ?? null) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Quantity Purchased</label>
                    <input type="number" step="0.01" min="0.01" name="quantity_purchased" x-model.number="qty" value="{{ old('quantity_purchased', $purchase->quantity ?? '') }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Price per unit ({{ $baseCurrency }})</label>
                    <input type="number" step="0.0001" min="0" name="purchase_price" x-model.number="price" value="{{ old('purchase_price', $prefillUnitPrice) }}" required class="field w-full">
                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Price for one unit, not the whole batch.</p>
                    @if ($purchase && $purchase->currency && $purchase->currency !== $baseCurrency)
                        <p class="text-xs mt-1" style="color:var(--ink-soft);">
                            Converted from {{ $purchase->currency }} {{ number_format($purchase->price_per_unit ?: ($purchase->total_price / max($purchase->quantity, 0.01)), 4) }} per unit
                            (order total {{ number_format($purchase->total_price, 2) }}) at rate {{ $purchase->exchange_rate }} â€” double-check before saving.
                        </p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Shipping Cost, total (optional)</label>
                    <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shipping" value="{{ old('shipping_cost', $purchase ? round((float) $purchase->shipping_cost * (float) $purchase->exchange_rate, 2) : 0) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Reorder Level (optional)</label>
                    <input type="number" step="0.01" min="0" name="reorder_level" value="{{ old('reorder_level') }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->arrived_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="field w-full">{{ old('notes', $purchase->notes ?? '') }}</textarea>
                </div>

                <div class="col-span-2 rounded-md p-3 text-sm" style="background:var(--paper); border:1px solid var(--line); color:var(--ink-soft);">
                    <div class="flex justify-between">
                        <span><span x-text="qty || 0"></span> &times; <span x-text="fmt(parseFloat(price) || 0)"></span></span>
                        <strong style="color:var(--ink);" x-text="'{{ $baseCurrency }} ' + fmt(itemsTotal)"></strong>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span>Shipping</span>
                        <strong style="color:var(--ink);" x-text="'{{ $baseCurrency }} ' + fmt(parseFloat(shipping) || 0)"></strong>
                    </div>
                    <div class="flex justify-between mt-1 pt-1" style="border-top:1px solid var(--line);">
                        <span class="font-semibold" style="color:var(--ink);">Total price of this purchase</span>
                        <strong style="color:var(--ink);" x-text="'{{ $baseCurrency }} ' + fmt(grandTotal)"></strong>
                    </div>
                </div>

                <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                    Add Supply
                </button>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>