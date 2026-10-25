@php
    // supply id => unit (pcs, meters, cm...) so the form can show the unit for existing supplies
    $supplyUnitMap = $supplies->pluck('unit', 'id');
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);" x-data="{
        baseCurrency: '{{ $baseCurrency }}',
        currency: '{{ old('currency', $baseCurrency) }}',
        exchangeRate: {{ old('exchange_rate', 1) ?: 1 }},
        shippingCost: {{ old('shipping_cost', 0) ?: 0 }},
        paidNow: '',
get grandTotalOrder() {
    return this.valueSum + (parseFloat(this.shippingCost) || 0);
},
get paidOrder() {
    const raw = parseFloat(this.paidNow);
    const p = isNaN(raw) ? this.grandTotalOrder : raw;
    return Math.min(Math.max(p, 0), this.grandTotalOrder);
},
get dueOrder() {
    return this.grandTotalOrder - this.paidOrder;
},
get dueBase() {
    return (this.dueOrder * this.effectiveRate).toFixed(2);
},
        supplyUnits: @js($supplyUnitMap),
        items: [
            { itemType: 'supply', supplyMode: 'existing', supply_id: '', name: '', category: '', unit: '', filament_type: '', filament_color: '', filament_brand: '', quantity: 0, total_price: 0 }
        ],

        get isBaseCurrency() {
            return this.currency === this.baseCurrency;
        },
        get effectiveRate() {
            return this.isBaseCurrency ? 1 : (parseFloat(this.exchangeRate) || 0);
        },
        get valueSum() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.total_price) || 0), 0);
        },
        // Unit for SUPPLY items only (existing supply's unit, or the unit picked for a new one)
        supplyUnitOf(item) {
            if (item.itemType !== 'supply') return '';
            if (item.supplyMode === 'existing') return this.supplyUnits[item.supply_id] || '';
            return item.unit || '';
        },
        shareOf(item) {
            const v = parseFloat(item.total_price) || 0;
            return this.valueSum > 0 ? (v / this.valueSum) : (1 / this.items.length);
        },
        shippingFor(item) {
            return (parseFloat(this.shippingCost || 0) * this.shareOf(item));
        },
        pricePerUnit(item) {
            const q = parseFloat(item.quantity) || 0;
            const t = parseFloat(item.total_price) || 0;
            return q > 0 ? (t / q) : 0;
        },
        landedPricePerUnit(item) {
            const q = parseFloat(item.quantity) || 0;
            const t = parseFloat(item.total_price) || 0;
            const s = this.shippingFor(item);
            return q > 0 ? ((t + s) / q) : 0;
        },
        totalInBase(item) {
            return ((parseFloat(item.total_price) || 0) * this.effectiveRate).toFixed(2);
        },
        get itemsTotalBase() {
            return this.items.reduce((sum, i) => sum + ((parseFloat(i.total_price) || 0) * this.effectiveRate), 0);
        },
        get shippingTotalBase() {
            return (parseFloat(this.shippingCost) || 0) * this.effectiveRate;
        },
        get grandTotalBase() {
            return (this.itemsTotalBase + this.shippingTotalBase).toFixed(2);
        },
        addItem() {
            this.items.push({ itemType: 'supply', supplyMode: 'existing', supply_id: '', name: '', category: '', unit: '', filament_type: '', filament_color: '', filament_brand: '', quantity: 0, total_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        }
    }"
    x-init="$watch('currency', value => { if (value === baseCurrency) exchangeRate = 1; })">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('purchases.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to purchases</a>
        <h1 class="ledger-font text-3xl mt-2 mb-6" style="color:var(--ink);">Log a Purchase</h1>

        <p class="text-sm mb-6" style="color:var(--ink-soft);">
            This just records the order. It won't touch stock or cost until you mark each item arrived.
        </p>

        @if ($errors->any())
            <div class="rounded-lg p-4 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-lg p-6 mb-4 grid grid-cols-2 gap-4" style="background:var(--surface); border:1px solid var(--line);">

                    <div class="col-span-2 flex justify-between items-center" x-show="items.length > 1">
                        <span class="text-xs font-semibold uppercase tracking-wide" style="color:var(--ink-soft);">
                            Item <span x-text="index + 1"></span>
                        </span>
                        <button type="button" @click="removeItem(index)" class="text-xs font-medium" style="color:var(--danger);">
                            Remove
                        </button>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">What are you buying?</label>
                        <div class="flex gap-2">
                            <button type="button" @click="item.itemType = 'supply'"
                                class="flex-1 text-sm font-medium px-3 py-2 rounded-md"
                                :style="item.itemType === 'supply' ? 'background:var(--accent); color:#FFF;' : 'background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);'">
                                Supply (wire, glue, bulb...)
                            </button>
                            <button type="button" @click="item.itemType = 'filament'"
                                class="flex-1 text-sm font-medium px-3 py-2 rounded-md"
                                :style="item.itemType === 'filament' ? 'background:var(--accent); color:#FFF;' : 'background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);'">
                                Filament spool
                            </button>
                        </div>
                        <input type="hidden" :name="'items['+index+'][item_type]'" x-model="item.itemType">
                    </div>

                    {{-- SUPPLY fields --}}
                    <template x-if="item.itemType === 'supply'">
                        <div class="col-span-2 grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <div class="flex gap-4 mb-2">
                                    <label class="flex items-center gap-1.5 text-sm" style="color:var(--ink-soft);">
                                        <input type="radio" :name="'supplyMode'+index" x-model="item.supplyMode" value="existing"> Existing item
                                    </label>
                                    <label class="flex items-center gap-1.5 text-sm" style="color:var(--ink-soft);">
                                        <input type="radio" :name="'supplyMode'+index" x-model="item.supplyMode" value="new"> Brand new item
                                    </label>
                                </div>
                            </div>

                            <template x-if="item.supplyMode === 'existing'">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supply</label>
                                    <select :name="'items['+index+'][supply_id]'" x-model="item.supply_id" class="field w-full">
                                        <option value="">Select an item</option>
                                        @foreach ($supplies as $supply)
                                            <option value="{{ $supply->id }}">{{ $supply->name }} ({{ $supply->unit }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);" x-show="item.supply_id"
                                       x-text="'Stock is tracked in ' + supplyUnitOf(item) + ' â€” enter the quantity in ' + supplyUnitOf(item) + '.'"></p>
                                </div>
                            </template>

                            <template x-if="item.supplyMode === 'new'">
                                <div class="col-span-2 grid grid-cols-2 gap-4">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Name</label>
                                        <input :name="'items['+index+'][name]'" x-model="item.name" class="field w-full" placeholder="e.g. Keyring loops 20mm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Category</label>
                                        <select :name="'items['+index+'][category]'" x-model="item.category" class="field w-full">
                                            <option value="">Uncategorized</option>
                                            @foreach ($supplyCategories as $category)
                                                <option value="{{ $category }}">{{ $category }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Unit</label>
                                        <select :name="'items['+index+'][unit]'" x-model="item.unit" class="field w-full">
                                            <option value="">Select unit</option>
                                            @foreach ($supplyUnits as $unit)
                                                <option value="{{ $unit }}">{{ $unit }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- FILAMENT fields --}}
                    <template x-if="item.itemType === 'filament'">
                        <div class="col-span-2 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Type</label>
                                <select :name="'items['+index+'][filament_type]'" x-model="item.filament_type" class="field w-full">
                                    <option value="">Select type</option>
                                    @foreach ($filamentTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Color</label>
                                <input :name="'items['+index+'][filament_color]'" x-model="item.filament_color" class="field w-full">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Brand (optional)</label>
                                <input :name="'items['+index+'][filament_brand]'" x-model="item.filament_brand" class="field w-full">
                            </div>
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                            Quantity
                            {{-- Filament: grams (unchanged) --}}
                            <span x-show="item.itemType === 'filament'">(grams)</span>
                            {{-- Supply only: show the unit (pcs, meters, cm...) --}}
                            <template x-if="item.itemType === 'supply'">
                                <span>
                                    <span x-show="supplyUnitOf(item)" x-text="'(' + supplyUnitOf(item) + ')'"></span>
                                    <span x-show="!supplyUnitOf(item)" style="color:var(--danger);">(pick a unit)</span>
                                </span>
                            </template>
                        </label>
                        <input type="number" step="0.01" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" required class="field w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
                            Total Price <span x-text="'(' + currency + ', this item)'"></span>
                        </label>
                        <input type="number" step="0.01" :name="'items['+index+'][total_price]'" x-model.number="item.total_price" required class="field w-full">
                    </div>

                    <div class="col-span-2 rounded-md p-3 text-sm" style="background:var(--paper); border:1px solid var(--line); color:var(--ink-soft);">
                        <div class="flex justify-between">
                            <span>Total in <span x-text="baseCurrency"></span></span>
                            <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + totalInBase(item)"></strong>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span>Shipping share <span x-text="'(' + currency + ')'"></span></span>
                            <strong style="color:var(--ink);" x-text="currency + ' ' + shippingFor(item).toFixed(2)"></strong>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span x-text="item.itemType === 'filament'
                                ? 'Price per gram (incl. shipping) in ' + baseCurrency
                                : 'Price per ' + (supplyUnitOf(item) || 'unit') + ' in ' + baseCurrency"></span>
                            <strong style="color:var(--ink);"
                                x-text="baseCurrency + ' ' + ((item.itemType === 'filament' ? landedPricePerUnit(item) : pricePerUnit(item)) * effectiveRate).toFixed(4)"></strong>
                        </div>
                    </div>
                </div>
            </template>

            <button type="button" @click="addItem()"
                class="text-sm font-semibold px-4 py-2.5 rounded-md mb-6 w-full"
                style="color:var(--accent); border:1px dashed var(--accent); background:var(--surface);">
                + Add another item
            </button>

            <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Supplier</label>
                        <select name="supplier_id" class="field w-full">
                            <option value="">No supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Paid from</label>
                        <select name="account_id" class="field w-full">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id', $accounts->firstWhere('type', 'cash')?->id) == $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Order Date</label>
                        <input type="date" name="order_date" value="{{ old('order_date') }}" class="field w-full">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Currency</label>
                        <select name="currency" x-model="currency" class="field w-full">
                            @foreach ($currencies as $code => $label)
                                <option value="{{ $code }}" @selected(old('currency', $baseCurrency) === $code)>{{ $code }} &mdash; {{ $label }}</option>
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
                            Shipping Cost, total <span x-text="'(' + currency + ', optional)'"></span>
                        </label>
                        <input type="number" step="0.01" name="shipping_cost" x-model.number="shippingCost" class="field w-full">
                        <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Split automatically across the items above by price share.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Notes (optional)</label>
                        <input name="notes" value="{{ old('notes') }}" class="field w-full">
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
                        <div class="col-span-2">
    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">
        Paying now <span x-text="'(' + currency + ')'"></span>
    </label>
    <input type="number" step="0.01" min="0" name="amount_paid" x-model="paidNow"
        :placeholder="'Full amount: ' + grandTotalOrder.toFixed(2)" class="field w-full">
    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">
        Leave empty to pay in full. Anything unpaid is recorded as owed to the supplier (a supplier must be selected).
    </p>
</div>
                        <div class="flex justify-between mt-1 pt-1" style="border-top:1px solid var(--line);">
                            <span class="font-semibold" style="color:var(--ink);">Grand total</span>
                            <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + grandTotalBase"></strong>
                        </div>
                    </div>

                    <div class="flex justify-between mt-1">
    <span>Paying now</span>
    <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + (paidOrder * effectiveRate).toFixed(2)"></strong>
</div>
<div class="flex justify-between mt-1" x-show="dueOrder > 0.005">
    <span class="font-semibold" style="color:var(--danger);">Due to supplier</span>
    <strong style="color:var(--danger);" x-text="baseCurrency + ' ' + dueBase"></strong>
</div>

                    <button class="text-sm font-semibold px-5 py-2.5 rounded-md col-span-2 w-fit" style="background:var(--accent); color:#FFF;">
                        Log Purchase
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>