<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-lg mx-auto px-6 py-10">

        <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to expenses
        </a>

        <h1 class="ledger-font text-3xl mb-6" style="color:var(--ink);">New Expense</h1>
        <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4 rounded-lg p-6"
    style="background:var(--surface); border:1px solid var(--line);"
    x-data="{
        baseCurrency: '{{ $baseCurrency }}',
        currency: '{{ old('currency', $baseCurrency) }}',
        exchangeRate: {{ old('exchange_rate', 1) ?: 1 }},
        amount: '{{ old('amount') }}',
        get isBase() { return this.currency === this.baseCurrency; },
        get rate() { return this.isBase ? 1 : (parseFloat(this.exchangeRate) || 0); },
        get inBase() { return ((parseFloat(this.amount) || 0) * this.rate).toFixed(2); }
    }"
    x-init="$watch('currency', v => { if (v === baseCurrency) exchangeRate = 1; })">
            @csrf

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Date</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required class="field w-full">
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Category</label>
                <select name="expense_category_id" required class="field w-full">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('expense_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Account (paid from)</label>
                <select name="account_id" required class="field w-full">
                    <option value="">Select account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Description</label>
                <input type="text" name="description" value="{{ old('description') }}" required class="field w-full">
            </div>

            <div class="grid grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Currency</label>
        <select name="currency" x-model="currency" class="field w-full">
            @foreach ($currencies as $code => $label)
                <option value="{{ $code }}" @selected(old('currency', $baseCurrency) === $code)>{{ $code }} &mdash; {{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium block mb-1" style="color:var(--ink);">
            Exchange rate
            <span x-show="!isBase" x-text="'(1 ' + currency + ' = ? ' + baseCurrency + ')'"></span>
            <span x-show="isBase">(same as base)</span>
        </label>
        <input type="number" step="0.000001" min="0.000001" name="exchange_rate"
            x-model.number="exchangeRate" :readonly="isBase" class="field w-full"
            :style="isBase ? 'background:var(--paper); color:var(--ink-soft); cursor:not-allowed;' : ''">
    </div>
</div>

<div>
    <label class="text-sm font-medium block mb-1" style="color:var(--ink);">
        Amount <span x-text="'(' + currency + ')'"></span>
    </label>
    <input type="number" step="0.01" name="amount" x-model.number="amount" required class="field w-full">
    <p class="text-xs mt-1" style="color:var(--ink-soft);" x-show="!isBase">
        Deducted from the account: <strong style="color:var(--ink);" x-text="baseCurrency + ' ' + inBase"></strong>
    </p>
</div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Payment method</label>
                <select name="payment_method" required class="field w-full">
                    <option value="cash" @selected(old('payment_method') == 'cash')>Cash</option>
                    <option value="bank" @selected(old('payment_method') == 'bank')>Bank</option>
                    <option value="mobile_money" @selected(old('payment_method') == 'mobile_money')>Mobile Money</option>
                    <option value="other" @selected(old('payment_method') == 'other')>Other</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Supplier (optional)</label>
                <select name="supplier_id" class="field w-full">
                    <option value="">None</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Notes (optional)</label>
                <textarea name="notes" rows="3" class="field w-full">{{ old('notes') }}</textarea>
            </div>

            @if ($errors->any())
                <div class="text-sm" style="color:var(--danger);">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('expenses.index') }}" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</a>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Save Expense</button>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>