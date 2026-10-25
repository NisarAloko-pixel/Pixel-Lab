<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-lg mx-auto px-6 py-10">
        <a href="{{ route('finance.transaction') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to transactions
        </a>
        <h1 class="ledger-font text-3xl mb-6" style="color:var(--ink);">New Transaction</h1>

        <form method="POST" action="{{ route('finance.transaction.store') }}" class="space-y-4 rounded-lg p-6"
            style="background:var(--surface); border:1px solid var(--line);">
            @csrf

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Account</label>
                <select name="account_id" required class="field w-full">
                    <option value="">Select account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Type</label>
                <select name="type" required class="field w-full">
                    <option value="income">Income (money in)</option>
                    <option value="expense">Expense (money out)</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Date</label>
                <input type="date" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required class="field w-full">
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Amount</label>
                <input type="number" step="0.01" name="amount" required class="field w-full">
            </div>

            <div>
                <label class="text-sm font-medium block mb-1" style="color:var(--ink);">Description</label>
                <input type="text" name="description" required class="field w-full" placeholder="e.g. Owner capital injection">
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
                <a href="{{ route('finance.transaction') }}" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</a>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Save</button>
            </div>
        </form>
    </div>
</div>
</x-layouts.pixel>