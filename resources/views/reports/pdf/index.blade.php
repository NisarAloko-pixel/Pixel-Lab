<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin-top: 24px; margin-bottom: 6px; }
        p.sub { color: #666; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .num { text-align: right; }
        .summary-grid { width: 100%; }
        .summary-grid td { border: none; padding: 4px 12px 4px 0; }
    </style>
</head>
<body>
    <h1>Reports Overview</h1>
    <p class="sub">{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>

    <table class="summary-grid">
        <tr>
            <td><strong>Revenue:</strong> {{ number_format($summary['revenue'], 2) }}</td>
            <td><strong>Net Profit:</strong> {{ number_format($summary['net_profit'], 2) }}</td>
            <td><strong>Operating Expenses:</strong> {{ number_format($summary['operating_expenses'], 2) }}</td>
            <td><strong>Margin:</strong> {{ number_format($summary['profit_margin_pct'], 1) }}%</td>
        </tr>
    </table>

    <h2>Monthly Trend</h2>
    <table>
        <thead>
            <tr><th>Month</th><th class="num">Revenue</th><th class="num">Expenses</th><th class="num">Net Profit</th></tr>
        </thead>
        <tbody>
            @foreach ($months as $m)
                <tr>
                    <td>{{ $m['label'] }}</td>
                    <td class="num">{{ number_format($m['revenue'], 2) }}</td>
                    <td class="num">{{ number_format($m['expenses'], 2) }}</td>
                    <td class="num">{{ number_format($m['net_profit'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Expense Breakdown</h2>
    <table>
        <thead><tr><th>Category</th><th class="num">Amount</th></tr></thead>
        <tbody>
            @forelse ($expenseBreakdown as $category => $amount)
                <tr><td>{{ $category }}</td><td class="num">{{ number_format($amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="2">No expenses in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top Products by Profit</h2>
    <table>
        <thead><tr><th>Product</th><th class="num">Profit</th></tr></thead>
        <tbody>
            @forelse ($topProducts as $product)
                <tr><td>{{ $product->name }}</td><td class="num">{{ number_format($product->profit, 2) }}</td></tr>
            @empty
                <tr><td colspan="2">No product sales in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>