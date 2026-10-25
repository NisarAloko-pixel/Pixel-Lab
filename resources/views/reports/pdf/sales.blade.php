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
    <h1>Sales Report</h1>
    <p class="sub">{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="num">Total</th>
                <th class="num">Cost</th>
                <th class="num">Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ optional($sale->sold_at)->format('Y-m-d') }}</td>
                    <td>{{ $sale->customer->name ?? '—' }}</td>
                    <td class="num">{{ number_format($sale->total, 2) }}</td>
                    <td class="num">{{ number_format($sale->cost_total, 2) }}</td>
                    <td class="num">{{ number_format($sale->gross_profit, 2) }}</td>
                    <td>{{ $sale->payment_status }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No sales in this period.</td></tr>
            @endforelse
        </tbody>
        @if ($sales->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3">Totals</td>
                    <td class="num">{{ number_format($sales->sum('total'), 2) }}</td>
                    <td class="num">{{ number_format($sales->sum('cost_total'), 2) }}</td>
                    <td class="num">{{ number_format($sales->sum('gross_profit'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>