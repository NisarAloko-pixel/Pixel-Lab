<x-layouts.pixel>
<style>
    /* Typed text and dropdown lists follow the theme */
    #sale_form input, #sale_form select, #sale_form textarea { color: var(--ink); }
    #sale_form select option { background: var(--surface); color: var(--ink); }
    html[data-theme="dark"] #sale_form input,
    html[data-theme="dark"] #sale_form select,
    html[data-theme="dark"] #sale_form textarea { color-scheme: dark; }
    #sale_form ::placeholder { color: var(--ink-soft); opacity: .6; }

    /* Read-only / disabled fields: slightly tinted, readable in both themes */
    #sale_form input:disabled,
    #sale_form input[readonly] {
        background: color-mix(in srgb, var(--ink) 8%, var(--paper)) !important;
        color: var(--ink-soft) !important;
        -webkit-text-fill-color: var(--ink-soft);
        opacity: 1;
    }

    /* Order total card */
    .total-card { background: var(--surface); border: 1px solid var(--line); border-top: 3px solid var(--accent); color: var(--ink); }
    .total-card .t-label { color: var(--ink-soft); }
</style>

<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to sales
        </a>

        <div class="mb-8">
            <h1 class="ledger-font text-3xl" style="color:var(--ink);">New Sale</h1>
            <p class="text-sm mt-1" style="color:var(--ink-soft);">Record items, payment, and totals for this order.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-md p-4 mb-6" style="background:var(--danger-soft); border:1px solid var(--line); color:var(--danger);">
                <p class="text-sm font-medium mb-1">Please fix the following before continuing:</p>
                <ul class="list-disc pl-5 text-sm space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('sales.store') }}" id="sale_form" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            @csrf

            {{-- LEFT: main content --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Customer & payment --}}
                <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
                    <h2 class="ledger-font text-lg mb-4">Sale details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Customer</label>
                            <select name="customer_id" class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                                <option value="">Walk-in customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Payment method</label>
                            <select name="payment_method" class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="ledger-font text-lg">Items</h2>
                        <button type="button" id="add_item_btn"
                            class="text-sm font-medium px-3 py-1.5 rounded-md"
                            style="color:var(--accent-ink); background:var(--accent-soft);">
                            + Add item
                        </button>
                    </div>

                    <div id="items_container" class="space-y-4"></div>
                </div>

                <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
                    <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);" placeholder="Anything worth remembering about this order..."></textarea>
                </div>
            </div>

            {{-- RIGHT: frozen summary panel, pinned in the viewport as the left column scrolls --}}
            <div class="lg:sticky self-start space-y-6" style="top: 1.5rem;">

                <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
                    <h2 class="ledger-font text-lg mb-4">Adjustments</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Overall discount</label>
                            <input type="number" step="0.01" name="discount" id="overall_discount" value="0"
                                class="w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color:var(--ink-soft);">Tax</label>
                            <input type="number" step="0.01" name="tax" id="overall_tax" value="0"
                                class="w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                        </div>
                    </div>
                </div>

                <div class="total-card rounded-lg p-6">
                    <h2 class="ledger-font text-lg mb-4">Order total</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="t-label">Items subtotal</span><span id="preview_subtotal" class="num">0.00</span></div>
                        <div class="flex justify-between"><span class="t-label">Discount</span><span id="preview_discount" class="num">-0.00</span></div>
                        <div class="flex justify-between"><span class="t-label">Tax</span><span id="preview_tax" class="num">+0.00</span></div>
                        <div class="flex justify-between pt-3 mt-2 ledger-font text-xl" style="border-top:1px solid var(--line);">
                            <span>Grand total</span><span id="preview_total" class="num">0.00</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 space-y-4" style="border-top:1px solid var(--line);">
                        <div>
                            <label class="t-label block text-sm mb-1">Amount paid</label>
                            <input type="number" step="0.01" name="amount_paid" id="amount_paid" value="0"
                                class="w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2"
                                style="background:var(--paper); border:1px solid var(--line); --tw-ring-color:var(--accent);">
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="t-label">Remaining</span>
                            <span id="remaining_amount_display" class="num font-semibold">0.00</span>
                            <input type="hidden" id="remaining_amount" value="0.00">
                        </div>
                    </div>

                    <div id="stock_error_banner" class="hidden mt-4 rounded-md px-3 py-2 text-xs font-medium" style="background:var(--danger-soft); color:var(--danger);">
                        One or more items or supplies exceed available stock. Fix the highlighted quantity before continuing.
                    </div>

                    <button type="submit"
                        class="w-full mt-6 rounded-md py-2.5 text-sm font-semibold"
                        style="background:var(--accent); color:#FFF;">
                        Create sale
                    </button>
                </div>

                <a href="{{ route('sales.index') }}" class="block text-center text-sm font-medium" style="color:var(--ink-soft);">Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Hidden template for a product select's option data, used to populate each new row --}}
<template id="product_options_template">
    <option value="">Select a product</option>
    @foreach ($products as $product)
        <option value="{{ $product->id }}"
            data-name="{{ $product->name }}"
            data-unit="{{ $product->unit }}"
            data-grams="{{ $product->grams_used ?? 0 }}"
            data-hours="{{ $product->print_hours ?? 0 }}"
            data-cost="{{ $product->cost_price ?? 0 }}"
            data-price="{{ $product->selling_price ?? 0 }}"
            data-stock="{{ $product->current_stock ?? 0 }}"
            data-damaged="{{ $product->damaged_stock ?? 0 }}">
            {{ $product->name }} ({{ $product->sku }})
        </option>
    @endforeach
</template>

{{-- Hidden template for a supply select's option data, used to populate each new "extra" row --}}
<template id="supply_options_template">
    <option value="">Custom item (not tracked in supplies)</option>
    @foreach ($supplies as $supply)
        <option value="{{ $supply->id }}"
            data-name="{{ $supply->name }}"
            data-unit="{{ $supply->unit }}"
            data-stock="{{ $supply->quantity_remaining }}"
            data-cost="{{ $supply->cost_per_unit }}">
            {{ $supply->name }} ({{ number_format($supply->quantity_remaining, 2) }} {{ $supply->unit }} left)
        </option>
    @endforeach
</template>

{{-- Row template rendered by JS for each item --}}
<template id="item_row_template">
    <div class="rounded-lg p-5" style="border:1px solid var(--line); background:var(--paper);">
        <div class="flex justify-between items-center mb-3">
            <span class="text-xs font-semibold uppercase tracking-wide item-number" style="color:var(--ink-soft);"></span>
            <button type="button" class="text-xs font-medium remove-item-btn" style="color:var(--danger);">Remove</button>
        </div>

        <select class="product-select w-full rounded-md px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--surface); --tw-ring-color:var(--accent);"></select>

        <div class="damaged-warning hidden rounded-md px-3 py-2 mb-3 text-xs font-medium" style="background:var(--warn-soft); color:var(--warn);"></div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-3 text-xs">
            <div>
                <span class="block mb-1" style="color:var(--ink-soft);">Name</span>
                <input type="text" class="display-name w-full rounded-md px-2 py-1.5 num" disabled style="border:1px solid var(--line);">
            </div>
            <div>
                <span class="block mb-1" style="color:var(--ink-soft);">Available stock</span>
                <input type="text" class="display-stock w-full rounded-md px-2 py-1.5 num" disabled style="border:1px solid var(--line);">
            </div>
            <div>
                <span class="block mb-1" style="color:var(--ink-soft);">Grams used</span>
                <input type="text" class="display-grams w-full rounded-md px-2 py-1.5 num" disabled style="border:1px solid var(--line);">
            </div>
            <div>
                <span class="block mb-1" style="color:var(--ink-soft);">Print hours</span>
                <input type="text" class="display-hours w-full rounded-md px-2 py-1.5 num" disabled style="border:1px solid var(--line);">
            </div>
            <div>
                <span class="block mb-1" style="color:var(--ink-soft);">Base cost</span>
                <input type="text" class="display-cost w-full rounded-md px-2 py-1.5 num" disabled style="border:1px solid var(--line);">
            </div>
        </div>

        <div class="stock-warning hidden rounded-md px-3 py-2 mb-3 text-xs font-medium" style="background:var(--danger-soft); color:var(--danger);"></div>

        <div class="grid grid-cols-3 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Quantity</label>
                <input type="number" step="0.001" min="0.001" class="quantity-input w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--surface); --tw-ring-color:var(--accent);" value="1">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Selling price</label>
                <input type="number" step="0.01" min="0" class="price-input w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--surface); --tw-ring-color:var(--accent);">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Discount</label>
                <input type="number" step="0.01" min="0" class="discount-input w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--surface); --tw-ring-color:var(--accent);" value="0">
            </div>

            {{-- Both inputs get the same name in addItemRow(): the hidden 0 is always sent,
                 and when the box is ticked its 1 comes after it and wins. --}}
            <label class="col-span-3 flex items-center gap-2 text-xs font-medium cursor-pointer" style="color:var(--ink-soft);">
                <input type="hidden" class="free-hidden" value="0">
                <input type="checkbox" class="free-checkbox" value="1">
                Sell this item for free (gift / Friend)
            </label>
        </div>

        <div class="mb-2">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-medium" style="color:var(--ink-soft);">Other items / supplies used (optional)</span>
                <button type="button" class="text-xs font-medium add-extra-btn" style="color:var(--accent-ink);">+ Add extra</button>
            </div>
            <div class="extras-container space-y-2"></div>
        </div>

        <div class="flex justify-end items-baseline gap-2 mt-3 pt-3" style="border-top:1px dashed var(--line);">
            <span class="free-badge hidden text-[11px] font-semibold px-2 py-0.5 rounded" style="background:var(--accent-soft); color:var(--accent-ink);">Free</span>
            <span class="text-xs" style="color:var(--ink-soft);">Line total</span>
            <span class="line-total-display ledger-font text-lg num">0.00</span>
        </div>
    </div>
</template>

{{--
    Row template for a single "extra" used within an item. Its quantity and cost are
    TOTALS for this whole line (e.g. "3100 pcs of ring, $20 total"), not a per-unit
    rate that gets multiplied by the item's own quantity.
--}}
<template id="extra_row_template">
    <div class="extra-row rounded-md p-2" style="border:1px solid var(--line); background:var(--surface);">
        <div class="flex gap-2 items-start flex-wrap">
            <select class="extra-supply-select rounded-md px-2 py-1.5 text-xs" style="min-width:11rem; flex:1 1 11rem; border:1px solid var(--line); background:var(--paper);"></select>
            <input type="text" class="extra-name-input rounded-md px-2 py-1.5 text-xs" style="min-width:8rem; flex:1 1 8rem; border:1px solid var(--line); background:var(--paper);" placeholder="Item name">
            <input type="number" step="0.001" min="0.001" class="extra-qty-input rounded-md px-2 py-1.5 text-xs num" style="width:5.5rem; border:1px solid var(--line); background:var(--paper);" placeholder="Qty used" value="1" title="Total quantity of this supply used for this whole line (not per item)">
            <input type="number" step="0.01" min="0" class="extra-cost-input rounded-md px-2 py-1.5 text-xs num" style="width:6rem; border:1px solid var(--line); background:var(--paper);" placeholder="Supply cost" title="Cost of this supply used for this line, for profit tracking only, not added to the customer's total">
            <button type="button" class="text-xs remove-extra-btn shrink-0" style="color:var(--danger);">&times;</button>
        </div>
        <div class="extra-stock-hint hidden text-xs mt-1" style="color:var(--ink-soft);"></div>
        <div class="extra-stock-warning hidden text-xs mt-1 font-medium" style="color:var(--danger);"></div>
    </div>
</template>

<script>
    let itemIndex = 0;

    function productOptionsHtml() {
        return document.getElementById('product_options_template').innerHTML;
    }

    function supplyOptionsHtml() {
        return document.getElementById('supply_options_template').innerHTML;
    }

    function addItemRow() {
        const index = itemIndex++;
        const container = document.getElementById('items_container');

        const fragment = document.getElementById('item_row_template').content.cloneNode(true);
        const row = fragment.querySelector('div');
        row.dataset.index = index;

        row.querySelector('.item-number').textContent = 'Item ' + (container.children.length + 1);

        const select = row.querySelector('.product-select');
        select.innerHTML = productOptionsHtml();
        select.name = `items[${index}][product_id]`;

        row.querySelector('.quantity-input').name = `items[${index}][quantity]`;
        row.querySelector('.price-input').name = `items[${index}][unit_price]`;
        row.querySelector('.discount-input').name = `items[${index}][discount]`;

        // The Free tick must have a name, otherwise the browser never submits it
        row.querySelector('.free-hidden').name = `items[${index}][is_free]`;
        row.querySelector('.free-checkbox').name = `items[${index}][is_free]`;

        container.appendChild(row);
        wireRowEvents(row, index);
    }

    function renumberItems() {
        document.querySelectorAll('#items_container > div').forEach((row, i) => {
            row.querySelector('.item-number').textContent = 'Item ' + (i + 1);
        });
    }

    // Reads the parent item's index back off its container's [data-index], so extra
    // rows can build correctly-namespaced input names without threading the index
    // through every call site.
    function itemIdxOf(extrasContainer) {
        const row = extrasContainer.closest('[data-index]');
        return row.dataset.index;
    }

    // Adds one "extra" row (supply-linked or custom) to an item's extras container.
    function addExtraRow(extrasContainer) {
        const nextIndex = extrasContainer.dataset.nextIndex ? parseInt(extrasContainer.dataset.nextIndex, 10) : 0;
        extrasContainer.dataset.nextIndex = nextIndex + 1;

        const itemIdx = itemIdxOf(extrasContainer);

        const fragment = document.getElementById('extra_row_template').content.cloneNode(true);
        const extraRow = fragment.querySelector('.extra-row');

        const supplySelect = extraRow.querySelector('.extra-supply-select');
        supplySelect.innerHTML = supplyOptionsHtml();
        supplySelect.name = `items[${itemIdx}][extras][${nextIndex}][supply_id]`;

        extraRow.querySelector('.extra-name-input').name = `items[${itemIdx}][extras][${nextIndex}][name]`;
        extraRow.querySelector('.extra-qty-input').name = `items[${itemIdx}][extras][${nextIndex}][quantity]`;
        extraRow.querySelector('.extra-cost-input').name = `items[${itemIdx}][extras][${nextIndex}][cost]`;

        extrasContainer.appendChild(extraRow);
        wireExtraRow(extraRow);
    }

    function wireExtraRow(extraRow) {
        const supplySelect = extraRow.querySelector('.extra-supply-select');
        const nameInput = extraRow.querySelector('.extra-name-input');
        const qtyInput = extraRow.querySelector('.extra-qty-input');
        const costInput = extraRow.querySelector('.extra-cost-input');
        const stockHint = extraRow.querySelector('.extra-stock-hint');
        const stockWarning = extraRow.querySelector('.extra-stock-warning');
        const removeBtn = extraRow.querySelector('.remove-extra-btn');

        function selectedOption() {
            return supplySelect.options[supplySelect.selectedIndex];
        }

        // Checks the TOTAL quantity entered for this extra against the selected supply's
        // available stock. Returns true if over stock (invalid), false otherwise
        // (including "no supply selected", since custom items aren't stock-tracked).
        function validateExtraStock() {
            const opt = selectedOption();

            if (!opt || !opt.value) {
                stockHint.classList.add('hidden');
                stockWarning.classList.add('hidden');
                qtyInput.style.borderColor = 'var(--line)';
                return false;
            }

            const availableStock = parseFloat(opt.dataset.stock || 0);
            const unit = opt.dataset.unit || '';
            const requested = parseFloat(qtyInput.value || 0);

            stockHint.textContent = `${availableStock} ${unit} available`;
            stockHint.classList.remove('hidden');

            if (requested > availableStock) {
                stockWarning.textContent = `⚠ Only ${availableStock} ${unit} available — this line needs ${requested} ${unit}.`;
                stockWarning.classList.remove('hidden');
                qtyInput.style.borderColor = 'var(--danger)';
                return true;
            }

            stockWarning.classList.add('hidden');
            qtyInput.style.borderColor = 'var(--line)';
            return false;
        }

        // When a supply is picked, lock the name to the supply's name and auto-fill the
        // TOTAL cost (qty entered × supply cost/unit); still editable afterward.
        // When switched back to "Custom item", unlock the name field for free entry.
        // (The look of the locked field comes from the CSS rule for input[readonly].)
        function applySupplyDefaults() {
            const opt = selectedOption();

            if (opt && opt.value) {
                nameInput.value = opt.dataset.name || '';
                nameInput.readOnly = true;

                const qty = parseFloat(qtyInput.value || 0);
                const unitCost = parseFloat(opt.dataset.cost || 0);
                costInput.value = (qty * unitCost).toFixed(2);
            } else {
                nameInput.readOnly = false;
            }

            validateExtraStock();
            recalculateAll();
        }

        supplySelect.addEventListener('change', applySupplyDefaults);
        qtyInput.addEventListener('input', applySupplyDefaults);
        costInput.addEventListener('input', recalculateAll);
        removeBtn.addEventListener('click', () => {
            extraRow.remove();
            recalculateAll();
        });

        // Exposed so the global stock check can re-validate this row without re-wiring it.
        extraRow._validateExtraStock = validateExtraStock;
    }

    function formatHours(decimalHours) {
        const totalMinutes = Math.round(parseFloat(decimalHours || 0) * 60);
        const h = Math.floor(totalMinutes / 60);
        const m = totalMinutes % 60;
        return `${h}h ${m}m`;
    }

    // Checks one row's quantity against the selected product's available stock.
    // Shows/hides the row's stock-warning box and toggles a red outline on the input.
    // Returns true if the row is over stock (invalid), false otherwise.
    function validateRowStock(row) {
        const productSelect = row.querySelector('.product-select');
        const opt = productSelect.options[productSelect.selectedIndex];
        const quantityInput = row.querySelector('.quantity-input');
        const warningBox = row.querySelector('.stock-warning');

        if (!opt || !opt.value) {
            warningBox.classList.add('hidden');
            warningBox.textContent = '';
            quantityInput.style.borderColor = 'var(--line)';
            return false;
        }

        const stock = parseFloat(opt.dataset.stock || 0);
        const unit = opt.dataset.unit || '';
        const quantity = parseFloat(quantityInput.value || 0);

        if (quantity > stock) {
            warningBox.textContent = `⚠ Only ${stock} ${unit} available — cannot add ${quantity} ${unit}.`;
            warningBox.classList.remove('hidden');
            quantityInput.style.borderColor = 'var(--danger)';
            return true;
        }

        warningBox.classList.add('hidden');
        warningBox.textContent = '';
        quantityInput.style.borderColor = 'var(--line)';
        return false;
    }

    function anyRowOverStock() {
        let overStock = false;
        document.querySelectorAll('#items_container > div').forEach(row => {
            if (validateRowStock(row)) overStock = true;

            row.querySelectorAll('.extra-row').forEach(extraRow => {
                if (extraRow._validateExtraStock && extraRow._validateExtraStock()) overStock = true;
            });
        });
        document.getElementById('stock_error_banner').classList.toggle('hidden', !overStock);
        return overStock;
    }

    function wireRowEvents(row, index) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const discountInput = row.querySelector('.discount-input');
        const addExtraBtn = row.querySelector('.add-extra-btn');
        const extrasContainer = row.querySelector('.extras-container');
        const removeBtn = row.querySelector('.remove-item-btn');
        const freeCheckbox = row.querySelector('.free-checkbox');
        const freeBadge = row.querySelector('.free-badge');

        function applyFree(free) {
            // readOnly, not disabled: disabled inputs aren't submitted and unit_price is required
            priceInput.readOnly = free;
            discountInput.readOnly = free;
            freeBadge.classList.toggle('hidden', !free);
        }

        freeCheckbox.addEventListener('change', () => {
            const free = freeCheckbox.checked;

            if (free) {
                priceInput.dataset.original = priceInput.value;
                discountInput.dataset.original = discountInput.value;
                priceInput.value = 0;
                discountInput.value = 0;
            } else {
                priceInput.value = priceInput.dataset.original ?? priceInput.value;
                discountInput.value = discountInput.dataset.original ?? 0;
            }

            applyFree(free);
            recalculateAll();
        });

        productSelect.addEventListener('change', () => {
            const opt = productSelect.options[productSelect.selectedIndex];
            const unit = opt.dataset.unit || '';
            const stock = parseFloat(opt.dataset.stock || 0);

            row.querySelector('.display-name').value = opt.dataset.name || '';
            row.querySelector('.display-stock').value = stock + (unit ? ' ' + unit : '');
            row.querySelector('.display-grams').value = (opt.dataset.grams || 0) + ' g';
            row.querySelector('.display-hours').value = formatHours(opt.dataset.hours);
            row.querySelector('.display-cost').value = opt.dataset.cost || 0;
            priceInput.dataset.original = opt.dataset.price || 0;
            priceInput.value = freeCheckbox.checked ? 0 : (opt.dataset.price || 0);
            quantityInput.max = stock;

            const damaged = parseFloat(opt.dataset.damaged || 0);
            const damagedBox = row.querySelector('.damaged-warning');
            if (damaged > 0) {
                damagedBox.textContent = `⚠ ${damaged} unit(s) of this product are set aside as damaged and are not included in the sellable stock shown.`;
                damagedBox.classList.remove('hidden');
            } else {
                damagedBox.classList.add('hidden');
                damagedBox.textContent = '';
            }

            validateRowStock(row);
            recalculateAll();
        });

        quantityInput.addEventListener('input', () => {
            validateRowStock(row);
            recalculateAll();
        });

        addExtraBtn.addEventListener('click', () => addExtraRow(extrasContainer));
        removeBtn.addEventListener('click', () => { row.remove(); renumberItems(); recalculateAll(); });

        [priceInput, discountInput].forEach(el => el.addEventListener('input', recalculateAll));
    }

    function recalculateAll() {
        let subtotal = 0;
        document.querySelectorAll('#items_container > div').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value || 0);
            const price = parseFloat(row.querySelector('.price-input').value || 0);
            const discount = parseFloat(row.querySelector('.discount-input').value || 0);

            // Extras are a COST (deducted from profit, used to track supply consumption),
            // not additional revenue; their cost is already priced into `price` above.
            // So they are NOT added to the line total shown to the customer.
            const lineTotal = (quantity * price) - discount;
            row.querySelector('.line-total-display').textContent = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const overallDiscount = parseFloat(document.getElementById('overall_discount').value || 0);
        const tax = parseFloat(document.getElementById('overall_tax').value || 0);
        const grandTotal = subtotal - overallDiscount + tax;

        document.getElementById('preview_subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('preview_discount').textContent = '-' + overallDiscount.toFixed(2);
        document.getElementById('preview_tax').textContent = '+' + tax.toFixed(2);
        document.getElementById('preview_total').textContent = grandTotal.toFixed(2);

        const amountPaid = parseFloat(document.getElementById('amount_paid').value || 0);
        const remaining = Math.max(grandTotal - amountPaid, 0).toFixed(2);
        document.getElementById('remaining_amount').value = remaining;
        document.getElementById('remaining_amount_display').textContent = remaining;

        anyRowOverStock();
    }

    document.getElementById('add_item_btn').addEventListener('click', addItemRow);
    document.getElementById('overall_discount').addEventListener('input', recalculateAll);
    document.getElementById('overall_tax').addEventListener('input', recalculateAll);
    document.getElementById('amount_paid').addEventListener('input', recalculateAll);

    document.getElementById('sale_form').addEventListener('submit', (event) => {
        if (anyRowOverStock()) {
            event.preventDefault();
            document.getElementById('stock_error_banner').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    addItemRow(); // start with one row
</script>
</x-layouts.pixel>