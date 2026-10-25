<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">
        <a href="{{ Route::has('reports.index') ? route('reports.index') : url()->previous() }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to reports
        </a>

        <h1 class="ledger-font text-3xl mb-1">Expense Report</h1>
        <p class="text-sm mb-6" style="color:var(--ink-soft);">
            {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
        </p>

        <div class="flex gap-2 mb-6">
    <a href="{{ route('reports.expenses.exportPdf', request()->query()) }}"
        class="text-sm font-semibold px-4 py-2 rounded-md"
        style="border:1px solid var(--line); color:var(--ink-soft);">
        Export PDF
    </a>
    <a href="{{ route('reports.expenses.exportExcel', request()->query()) }}"
        class="text-sm font-semibold px-4 py-2 rounded-md"
        style="border:1px solid var(--line); color:var(--ink-soft);">
        Export Excel
    </a>
</div>
        <div class="card-3d overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Category</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Description</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Amount</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Method</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">
                                {{ $expense->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $expense->description }}</td>
                            <td class="px-4 py-3 text-right num font-medium">{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No expenses in this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.pixel>
