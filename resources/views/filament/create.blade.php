<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('filament.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to filament inventory</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Add Filament Spool</h1>

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
            <form method="POST" action="{{ route('filament.store') }}" class="grid grid-cols-2 gap-4">
                @csrf

                @if ($purchase)
                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                @endif

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Type</label>
                    <select name="type" required class="field w-full">
                        <option value="" disabled @selected(!old('type', $purchase->filament_type ?? null))>Select type&hellip;</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(old('type', $purchase->filament_type ?? null) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Color</label>
                    <input name="color" value="{{ old('color', $purchase->filament_color ?? '') }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Brand (optional)</label>
                    <input name="brand" value="{{ old('brand', $purchase->filament_brand ?? '') }}" class="field w-full">
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
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Weight Purchased (g)</label>
                    <input type="number" step="0.01" min="0.01" name="weight_purchased_g" value="{{ old('weight_purchased_g', $purchase->quantity ?? '') }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Purchase Price ({{ $baseCurrency }})</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $purchase->total_price_base ?? '') }}" required class="field w-full">
                    @if ($purchase && $purchase->currency && $purchase->currency !== $baseCurrency)
                        <p class="text-xs mt-1" style="color:var(--ink-soft);">Converted from {{ $purchase->currency }} {{ number_format($purchase->total_price, 2) }} at rate {{ $purchase->exchange_rate }} â€” double-check before saving.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Shipping Cost ({{ $baseCurrency }})</label>
                    <input type="number" step="0.01" min="0" name="shipping_cost" value="{{ old('shipping_cost', $purchase ? round((float) $purchase->shipping_cost * (float) $purchase->exchange_rate, 2) : 0) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->arrived_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="field w-full">
                </div>

                <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                    Add Spool
                </button>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>