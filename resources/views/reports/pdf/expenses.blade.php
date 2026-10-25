<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.sub { color: #666; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .num { text-align: right; }
        tfoot td { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Expense Report</h1>
    <p class="sub">{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr><th>Date</th><th>Category</th><th>Description</th><th class="num">Amount</th><th>Method</th></tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                    <td>{{ $expense->category->name ?? 'Uncategorized' }}</td>
                    <td>{{ $expense->description }}</td>
                    <td class="num">{{ number_format($expense->amount, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No expenses in this period.</td></tr>
            @endforelse
        </tbody>
        @if ($expenses->isNotEmpty())
            <tfoot>
                <tr><td colspan="3">Total</td><td class="num">{{ number_format($expenses->sum('amount'), 2) }}</td><td></td></tr>
            </tfoot>
        @endif
    </table>
</body>
</html>