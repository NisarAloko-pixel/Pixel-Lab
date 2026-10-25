@php($appCurrency = \App\Models\Setting::get('currency', 'AF'))
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .sub { color: #777; margin-bottom: 14px; }
        .totals { margin-bottom: 14px; }
        .totals td { padding: 3px 12px 3px 0; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { text-align: left; font-size: 10px; text-transform: uppercase; color: #777; border-bottom: 2px solid #ccc; padding: 5px 4px; }
        table.data td { border-bottom: 1px solid #ddd; padding: 5px 4px; }
        .r { text-align: right; }
    </style>
</head>
<body>
    <h1>Purchase Report</h1>
    <div class="sub">{{ $isAll ? 'All purchases' : $from->format('Y-m-d').' to '.$to->format('Y-m-d') }} (cancelled purchases excluded)</div>

    <table class="totals">
        <tr>
            <td>Purchases: <strong>{{ $purchases['count'] }}</strong></td>
            <td>Total: <strong>{{ $appCurrency }} {{ number_format($purchases['total'], 2) }}</strong></td>
            <td>Paid: <strong>{{ $appCurrency }} {{ number_format($purchases['paid'], 2) }}</strong></td>
            <td>Owed: <strong>{{ $appCurrency }} {{ number_format($purchases['payable'], 2) }}</strong></td>
        </tr>
        <tr>
            <td>Filament: {{ $appCurrency }} {{ number_format($purchases['filament_total'], 2) }}</td>
            <td>Supplies: {{ $appCurrency }} {{ number_format($purchases['supplies_total'], 2) }}</td>
            <td>Shipping: {{ $appCurrency }} {{ number_format($purchases['shipping'], 2) }}</td>
            <td></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>#</th><th>Date</th><th>Item</th><th>Type</th><th>Supplier</th><th>Status</th>
                <th class="r">Total</th><th class="r">Paid</th><th class="r">Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases['rows'] as $r)
                <tr>
                    <td>{{ $r->purchase->id }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->purchase->order_date ?? $r->purchase->created_at)->format('Y-m-d') }}</td>
                    <td>{{ $r->name }}</td>
                    <td>{{ ucfirst($r->purchase->item_type) }}</td>
                    <td>{{ $r->purchase->supplier->name ?? '-' }}</td>
                    <td>{{ ucfirst($r->purchase->status ?: 'pending') }}</td>
                    <td class="r">{{ number_format($r->total, 2) }}</td>
                    <td class="r">{{ number_format($r->paid, 2) }}</td>
                    <td class="r">{{ number_format($r->due, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center; color:#777;">No purchases in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>