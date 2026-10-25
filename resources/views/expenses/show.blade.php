<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-lg mx-auto px-6 py-10">

        <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to expenses
        </a>

        <div class="flex justify-between items-center mb-6">
            <h1 class="ledger-font text-3xl" style="color:var(--ink);">Expense Details</h1>
            <a href="{{ route('expenses.edit', $expense) }}" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Edit</a>
        </div>
        <div class="rounded-lg p-6 space-y-4" style="background:var(--surface); border:1px solid var(--line);">
            <div class="flex justify-between">
                <span class="text-sm" style="color:var(--ink-soft);">Date</span>
                <span class="text-sm font-medium">{{ $expense->expense_date->format('Y-m-d') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm" style="color:var(--ink-soft);">Category</span>
                <span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">{{ $expense->category?->name ?? 'Uncategorized' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm" style="color:var(--ink-soft);">Account</span>
                <span class="text-sm font-medium">{{ $expense->account->name ?? 'â€”' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm" style="color:var(--ink-soft);">Description</span>
                <span class="text-sm font-medium text-right">{{ $expense->description }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm" style="color:var(--ink-soft);">Amount</span>
                <span class="ledger-font text-xl num">{{ number_format($expense->amount, 2) }}</span>
            </div>
            @if ($expense->currency && $expense->original_amount && (float) $expense->exchange_rate != 1.0)
    <div class="flex justify-between">
        <span class="text-sm" style="color:var(--ink-soft);">Original amount</span>
        <span class="text-sm font-medium num">{{ $expense->currency }} {{ number_format($expense->original_amount, 2) }} <span style="color:var(--ink-soft);">@ {{ rtrim(rtrim($expense->exchange_rate, '0'), '.') }}</span></span>
    </div>
@endif
            <div class="flex justify-between">
                <span class="text-sm" style="color:var(--ink-soft);">Payment method</span>
                <span class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</span>
            </div>
            @if ($expense->supplier)
                <div class="flex justify-between">
                    <span class="text-sm" style="color:var(--ink-soft);">Supplier</span>
                    <a href="{{ route('suppliers.show', $expense->supplier) }}" class="text-sm font-medium" style="color:var(--accent-ink);">{{ $expense->supplier->name }}</a>
                </div>
            @endif
            @if ($expense->notes)
                <div class="pt-2" style="border-top:1px solid var(--line);">
                    <span class="text-sm block mb-1" style="color:var(--ink-soft);">Notes</span>
                    <p class="text-sm">{{ $expense->notes }}</p>
                </div>
            @endif
            @if ($expense->creator)
                <div class="pt-2" style="border-top:1px solid var(--line);">
                    <span class="text-xs" style="color:var(--ink-soft);">Recorded by {{ $expense->creator->name }} on {{ $expense->created_at->format('Y-m-d H:i') }}</span>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="mt-4"
            onsubmit="return confirm('Delete this expense? This cannot be undone.');">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md w-full" style="border:1px solid var(--danger); color:var(--danger);">
                Delete Expense
            </button>
        </form>
    </div>
</div>
</x-layouts.pixel>