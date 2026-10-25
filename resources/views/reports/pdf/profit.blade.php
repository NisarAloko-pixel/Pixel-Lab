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
    </style>
</head>
<body>
    <h1>Profit Report</h1>
    <p class="sub">
        {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
        vs prior {{ $prevFrom->format('Y-m-d') }} to {{ $prevTo->format('Y-m-d') }}
    </p>

    <table>
        <thead>
            <tr><th>Metric</th><th class="num">Current</th><th class="num">Prior</th><th class="num">Change</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Revenue</td>
                <td class="num">{{ number_format($summary['revenue'], 2) }}</td>
                <td class="num">{{ number_format($previousSummary['revenue'], 2) }}</td>
                <td class="num">{{ is_null($comparison['revenue']) ? '—' : number_format($comparison['revenue'], 1).'%' }}</td>
            </tr>
            <tr>
                <td>Gross Profit</td>
                <td class="num">{{ number_format($summary['gross_profit'], 2) }}</td>
                <td class="num">{{ number_format($previousSummary['gross_profit'], 2) }}</td>
                <td class="num">{{ is_null($comparison['gross_profit']) ? '—' : number_format($comparison['gross_profit'], 1).'%' }}</td>
            </tr>
            <tr>
                <td>Net Profit</td>
                <td class="num">{{ number_format($summary['net_profit'], 2) }}</td>
                <td class="num">{{ number_format($previousSummary['net_profit'], 2) }}</td>
                <td class="num">{{ is_null($comparison['net_profit']) ? '—' : number_format($comparison['net_profit'], 1).'%' }}</td>
            </tr>
            <tr>
                <td>Profit Margin %</td>
                <td class="num">{{ number_format($summary['profit_margin_pct'], 1) }}</td>
                <td class="num">{{ number_format($previousSummary['profit_margin_pct'], 1) }}</td>
                <td class="num">{{ number_format($comparison['profit_margin_pct'], 1) }} pts</td>
            </tr>
            <tr><td>COGS</td><td class="num">{{ number_format($summary['cogs'], 2) }}</td><td></td><td></td></tr>
            <tr><td>Operating Expenses</td><td class="num">{{ number_format($summary['operating_expenses'], 2) }}</td><td></td><td></td></tr>
            <tr><td>Sales Count</td><td class="num">{{ $summary['sales_count'] }}</td><td></td><td></td></tr>
            <tr><td>Avg Order Value</td><td class="num">{{ number_format($summary['average_order_value'], 2) }}</td><td></td><td></td></tr>
        </tbody>
    </table>
</body>
</html>