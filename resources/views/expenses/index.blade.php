<x-layouts.pixel>
<style>
    :root { --btn-dark: #21262F; }
    [data-theme="dark"] { --btn-dark: #3A4050; }
</style>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">


        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Expenses</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Everything the business has spent, in one place.</p>
            </div>
            <a href="{{ route('expenses.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                New Expense
            </a>
        </div>

        <form method="GET" class="flex flex-wrap gap-2 mb-5">
            <input type="date" name="from" value="{{ request('from') }}" class="field" style="color:var(--ink);">
            <input type="date" name="to" value="{{ request('to') }}" class="field" style="color:var(--ink);">
            <select name="category_id" class="field" style="color:var(--ink);">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--btn-dark); color:#FFF;">
                Filter
            </button>
        </form>

        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Category</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Description</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Account</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Amount</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Method</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">
                                    {{ $expense->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $expense->description }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $expense->account->name ?? '—' }}</td>
<td class="px-4 py-3 text-right num font-medium">
    {{ number_format($expense->amount, 2) }}
    @if ($expense->currency && $expense->original_amount && (float) $expense->exchange_rate != 1.0)
        <div class="text-xs font-normal" style="color:var(--ink-soft);">{{ $expense->currency }} {{ number_format($expense->original_amount, 2) }}</div>
    @endif
</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-1">
                                    <a href="{{ route('expenses.show', $expense) }}" class="icon-btn" style="color:var(--ink-soft);" title="View">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('expenses.edit', $expense) }}" class="icon-btn" style="color:var(--accent-ink);" title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                        onsubmit="return confirm('Delete this expense? This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn" style="color:var(--danger);" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No expenses yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $expenses->links() }}</div>
    </div>
</div>
</x-layouts.pixel>