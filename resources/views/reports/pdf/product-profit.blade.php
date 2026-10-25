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
    <h1>Product Profit Report</h1>
    <p class="sub">{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th><th class="num">Qty Sold</th><th class="num">Revenue</th>
                <th class="num">Cost</th><th class="num">Profit</th><th class="num">Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php $margin = $row->revenue > 0 ? ($row->profit / $row->revenue) * 100 : 0; @endphp
                <tr>
                    <td>{{ $row->name }}</td>
                    <td class="num">{{ $row->quantity_sold }}</td>
                    <td class="num">{{ number_format($row->revenue, 2) }}</td>
                    <td class="num">{{ number_format($row->cost, 2) }}</td>
                    <td class="num">{{ number_format($row->profit, 2) }}</td>
                    <td class="num">{{ number_format($margin, 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="6">No sales in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>