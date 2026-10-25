<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('supplies.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to supplies</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Edit Supply</h1>

        <p class="text-sm mb-6" style="color:var(--ink-soft);">
            Quantity on hand isn't editable here â€” use "Adjust stock" on the supplies list
            to restock, use, or correct the remaining amount.
        </p>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <form method="POST" action="{{ route('supplies.update', $supply) }}" class="grid grid-cols-2 gap-4"
                x-data="{
                    qty: @js((float) $supply->quantity_remaining),
                    price: @js((float) old('purchase_price', $supply->purchase_price)),
                    shipping: @js((float) old('shipping_cost', $supply->shipping_cost)),
                    get stockValue() { return (parseFloat(this.qty) || 0) * (parseFloat(this.price) || 0); },
                    fmt(n) { return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                }">
                @csrf @method('PUT')

                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                    <input name="name" value="{{ old('name', $supply->name) }}" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                    <select name="category" class="field w-full">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', $supply->category) === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Unit</label>
                    <input type="text" value="{{ $supply->unit }}" readonly tabindex="-1" class="field w-full" style="background:var(--paper); color:var(--ink-soft); cursor:not-allowed;">
                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Fixed once created, so stock and costs stay consistent.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Brand (optional)</label>
                    <input name="brand" value="{{ old('brand', $supply->brand) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supplier</label>
                    <select name="supplier_id" class="field w-full">
                        <option value="">No supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $supply->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Price per {{ $supply->unit }}</label>
                    <input type="number" step="0.0001" min="0" name="purchase_price" x-model.number="price" value="{{ old('purchase_price', $supply->purchase_price) }}" required class="field w-full">
                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Price for one {{ $supply->unit }}, not the whole batch.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Shipping Cost, total (optional)</label>
                    <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shipping" value="{{ old('shipping_cost', $supply->shipping_cost) }}" class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Reorder Level (optional)</label>
                    <input type="number" step="0.01" min="0" name="reorder_level" value="{{ old('reorder_level', $supply->reorder_level) }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $supply->purchase_date?->format('Y-m-d')) }}" class="field w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="field w-full">{{ old('notes', $supply->notes) }}</textarea>
                </div>

                <div class="col-span-2 rounded-md p-3 text-sm" style="background:var(--paper); border:1px solid var(--line); color:var(--ink-soft);">
                    <div class="flex justify-between">
                        <span>On hand: <span x-text="fmt(parseFloat(qty) || 0)"></span> {{ $supply->unit }} &times; <span x-text="fmt(parseFloat(price) || 0)"></span></span>
                        <strong style="color:var(--ink);" x-text="fmt(stockValue)"></strong>
                    </div>
                    <div class="flex justify-between mt-1 pt-1" style="border-top:1px solid var(--line);">
                        <span class="font-semibold" style="color:var(--ink);">Value of stock on hand</span>
                        <strong style="color:var(--ink);" x-text="fmt(stockValue)"></strong>
                    </div>
                </div>

                <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>