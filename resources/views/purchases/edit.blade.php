@php
    // supply id => unit (pcs, meters, cm...) so the form can show the unit for existing supplies
    $supplyUnitMap = $supplies->pluck('unit', 'id');

    $paidBase = (float) $purchase->amount_paid;
    $costBase = (float) $purchase->total_cost_base;
    $wasFullyPaid = $paidBase >= $costBase;
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);" x-data="{
        itemType: '{{ old('item_type', $purchase->item_type) }}',
        supplyMode: '{{ old('supply_id', $purchase->supply_id) ? 'existing' : ($purchase->item_type === 'supply' ? 'new' : 'existing') }}',
        supplyId: '{{ old('supply_id', $purchase->supply_id) }}',
        newUnit: '{{ old('unit', $purchase->unit) }}',
        supplyUnits: @js($supplyUnitMap),
        baseCurrency: '{{ $baseCurrency }}',
        currency: '{{ old('currency', $purchase->currency) }}',
        quantity: {{ old('quantity', $purchase->quantity) ?: 0 }},
        totalPrice: {{ old('total_price', $purchase->total_price) ?: 0 }},
        shippingCost: {{ old('shipping_cost', $purchase->shipping_cost) ?: 0 }},
        exchangeRate: {{ old('exchange_rate', $purchase->exchange_rate) ?: 1 }},
        paidBase: {{ $paidBase }},
        wasFullyPaid: {{ $wasFullyPaid ? 'true' : 'false' }},

        get isBaseCurrency() {
            return this.currency === this.baseCurrency;
        },
        get effectiveRate() {
            return this.isBaseCurrency ? 1 : (parseFloat(this.exchangeRate) || 0);
        },
        // Unit for SUPPLY items only (existing supply's unit, or the unit picked for a new one)
        get supplyUnit() {
            if (this.itemType !== 'supply') return '';
            if (this.supplyMode === 'existing') return this.supplyUnits[this.supplyId] || '';
            return this.newUnit || '';
        },
        get pricePerUnit() {
            return this.quantity > 0 ? (this.totalPrice / this.quantity) : 0;
        },
        get landedPricePerUnit() {
            return this.quantity > 0 ? ((this.totalPrice + (parseFloat(this.shippingCost) || 0)) / this.quantity) : 0;
        },
        get totalInBase() {
            return (this.totalPrice * this.effectiveRate).toFixed(2);
        },
        get itemsTotalBase() {
            return this.totalPrice * this.effectiveRate;
        },
        get shippingTotalBase() {
            return (parseFloat(this.shippingCost) || 0) * this.effectiveRate;
        },
        get grandTotalBase() {
            return this.itemsTotalBase + this.shippingTotalBase;
        },
        // Same rule as the server: a fully paid purchase stays fully paid; partial payments stay as recorded.
        get newPaidBase() {
            return this.wasFullyPaid ? this.grandTotalBase : Math.min(this.paidBase, this.grandTotalBase);
        },
        get newDueBase() {
            return Math.max(this.grandTotalBase - this.newPaidBase, 0);
        }
    }"
    x-init="$watch('currency', value => { if (value === baseCurrency) exchangeRate = 1; })">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('purchases.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to purchases</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Edit Purchase #{{ $purchase->id }}</h1>

        <p class="text-sm mb-6" style="color:var(--ink-soft);">
            Still in transit â€” nothing here has touched stock or cost yet.
        </p>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('purchases.update', $purchase) }}">
            @csrf
            @method('PUT')

            {{-- ITEM CARD --}}
            <div class="rounded-lg p-6 mb-4 grid grid-cols-2 gap-4" style="background:var(--surface); border:1px solid var(--line);">

                <div class="col-span-2">
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">What are you buying?</label>
                    <div class="flex gap-2">
                        <button type="button" @click="itemType = 'supply'"
                            class="flex-1 text-sm font-medium px-3 py-2 rounded-md"
                            :style="itemType === 'supply' ? 'background:var(--accent); color:#FFF;' : 'background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);'">
                            Supply (wire, glue, bulb...)
                        </button>
                        <button type="button" @click="itemType = 'filament'"
                            class="flex-1 text-sm font-medium px-3 py-2 rounded-md"
                            :style="itemType === 'filament' ? 'background:var(--accent); color:#FFF;' : 'background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);'">
                            Filament spool
                        </button>
                    </div>
                    <input type="hidden" name="item_type" :value="itemType">
                </div>

                {{-- SUPPLY fields --}}
                <template x-if="itemType === 'supply'">
                    <div class="col-span-2 grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <div class="flex gap-4 mb-2">
                                <label class="flex items-center gap-1.5 text-sm" style="color:var(--ink-soft);">
                                    <input type="radio" x-model="supplyMode" value="existing"> Existing item
                                </label>
                                <label class="flex items-center gap-1.5 text-sm" style="color:var(--ink-soft);">
                                    <input type="radio" x-model="supplyMode" value="new"> Brand new item
                                </label>
                            </div>
                        </div>

                        <template x-if="supplyMode === 'existing'">
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supply</label>
                                <select name="supply_id" x-model="supplyId" class="field w-full">
                                    <option value="">Select an item</option>
                                    @foreach ($supplies as $supply)
                                        <option value="{{ $supply->id }}" @selected(old('supply_id', $purchase->supply_id) == $supply->id)>{{ $supply->name }} ({{ $supply->unit }})</option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] mt-1" style="color:var(--ink-soft);" x-show="supplyId"
                                   x-text="'Stock is tracked in ' + supplyUnit + ' â€” enter the quantity in ' + supplyUnit + '.'"></p>
                            </div>
                        </template>

                        <template x-if="supplyMode === 'new'">
                            <div class="col-span-2 grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                                    <input name="name" value="{{ old('name', $purchase->name) }}" class="field w-full" placeholder="e.g. Keyring loops 20mm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                                    <select name="category" class="field w-full">
                                        <option value="">Uncategorized</option>
                                        @foreach ($supplyCategories as $category)
                                            <option value="{{ $category }}" @selected(old('category', $purchase->category) === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Unit</label>
                                    <select name="unit" x-model="newUnit" class="field w-full">
                                        <option value="">Select unit</option>
                                        @foreach ($supplyUnits as $unit)
                                            <option value="{{ $unit }}" @selected(old('unit', $purchase->unit) === $unit)>{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- FILAMENT fields --}}
                <template x-if="itemType === 'filament'">
                    <div class="col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Type</label>
                            <select name="filament_type" class="field w-full">
                                <option value="">Select type</option>
                                @foreach ($filamentTypes as $type)
                                    <option value="{{ $type }}" @selected(old('filament_type', $purchase->filament_type) === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Color</label>
                            <input name="filament_color" value="{{ old('filament_color', $purchase->filament_color) }}" class="field w-full">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Brand (optional)</label>
                            <input name="filament_brand" value="{{ old('filament_brand', $purchase->filament_brand) }}" class="field w-full">
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                        Quantity
                        {{-- Filament: grams (unchanged) --}}
                        <span x-show="itemType === 'filament'">(grams)</span>
                        {{-- Supply only: show the unit (pcs, meters, cm...) --}}
                        <template x-if="itemType === 'supply'">
                            <span>
                                <span x-show="supplyUnit" x-text="'(' + supplyUnit + ')'"></span>
                                <span x-show="!supplyUnit" style="color:var(--danger);">(pick a unit)</span>
                            </span>
                        </template>
                    </label>
                    <input type="number" step="0.01" name="quantity" x-model.number="quantity" required class="field w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                        Total Price <span x-text="'(' + currency + ', this item)'"></span>
                    </label>
                    <input type="number" step="0.01" name="total_price" x-model.number="totalPrice" required class="field w-full">
                    <input type="hidden" name="price_per_unit" :value="pricePerUnit.toFixed(4)">
                </div>

                <div class="col-span-2 rounded-md p-3 text-sm" style="background:var(--paper); border:1px solid var(--line); color:var(--ink-soft);">
                    <div class="flex justify-between">
                        <span>Total in <span x-text="baseCurrency"></span></span>
                        <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + totalInBase"></strong>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span>Shipping <span x-text="'(' + currency + ')'"></span></span>
                        <strong style="color:var(--ink);" x-text="currency + ' ' + (parseFloat(shippingCost) || 0).toFixed(2)"></strong>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span x-text="itemType === 'filament'
                            ? 'Price per gram (incl. shipping) in ' + baseCurrency
                            : 'Price per ' + (supplyUnit || 'unit') + ' in ' + baseCurrency"></span>
                        <strong style="color:var(--ink);"
                            x-text="baseCurrency + ' ' + ((itemType === 'filament' ? landedPricePerUnit : pricePerUnit) * effectiveRate).toFixed(4)"></strong>
                    </div>
                </div>
            </div>

            {{-- ORDER CARD --}}
            <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supplier</label>
                        <select name="supplier_id" class="field w-full">
                            <option value="">No supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Order Date</label>
                        <input type="date" name="order_date" value="{{ old('order_date', $purchase->order_date?->format('Y-m-d')) }}" class="field w-full">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Currency</label>
                        <select name="currency" x-model="currency" class="field w-full">
                            @foreach ($currencies as $code => $label)
                                <option value="{{ $code }}" @selected(old('currency', $purchase->currency) === $code)>{{ $code }} &mdash; {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                            Exchange Rate
                            <span x-show="!isBaseCurrency" x-text="'(1 ' + currency + ' = ? ' + baseCurrency + ')'"></span>
                            <span x-show="isBaseCurrency">(same as base)</span>
                        </label>
                        <input type="number" step="0.000001" min="0.000001" name="exchange_rate"
                            x-model.number="exchangeRate"
                            :readonly="isBaseCurrency"
                            class="field w-full"
                            :style="isBaseCurrency ? 'background:var(--paper); color:var(--ink-soft); cursor:not-allowed;' : ''">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                            Shipping Cost <span x-text="'(' + currency + ', optional)'"></span>
                        </label>
                        <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost" class="field w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes (optional)</label>
                        <input name="notes" value="{{ old('notes', $purchase->notes) }}" class="field w-full">
                    </div>

                    <div class="col-span-2 rounded-md p-3 text-sm" style="background:var(--paper); border:1px solid var(--line); color:var(--ink-soft);">
                        <div class="flex justify-between">
                            <span>Items total in <span x-text="baseCurrency"></span></span>
                            <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + itemsTotalBase.toFixed(2)"></strong>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span>Shipping in <span x-text="baseCurrency"></span></span>
                            <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + shippingTotalBase.toFixed(2)"></strong>
                        </div>
                        <div class="flex justify-between mt-1 pt-1" style="border-top:1px solid var(--line);">
                            <span class="font-semibold" style="color:var(--ink);">Grand total</span>
                            <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + grandTotalBase.toFixed(2)"></strong>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span>Paid so far</span>
                            <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + newPaidBase.toFixed(2)"></strong>
                        </div>
                        <div class="flex justify-between mt-1" x-show="newDueBase > 0.005">
                            <span class="font-semibold" style="color:var(--danger);">Due to supplier</span>
                            <strong style="color:var(--danger);" x-text="baseCurrency + ' ' + newDueBase.toFixed(2)"></strong>
                        </div>
                    </div>

                    <p class="col-span-2 text-[11px]" style="color:var(--ink-soft);">
                        @if ($wasFullyPaid)
                            This purchase is paid in full, so it stays fully paid if you change the price.
                        @else
                            Payments already made stay as recorded. To pay the remaining amount, use the Pay button on the supplier's page.
                        @endif
                    </p>

                    <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>