@php
    $appCurrency = \App\Models\Setting::get('currency', 'AF');
@endphp

{{-- Purchases card for the main Reports page. Needs $purchases (period totals) and $recentPurchases (latest, any date). --}}
<div class="card-3d p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="ledger-font text-lg">Purchases</h2>
    </div>

  @php
    $periodPurchases = $recentPurchases->filter(function ($r) use ($from, $to) {
        $date = \Illuminate\Support\Carbon::parse($r->purchase->order_date ?? $r->purchase->created_at);
        return $date->between($from->copy()->startOfDay(), $to->copy()->endOfDay());
    });
    $periodTotal = $periodPurchases->sum('total');
    $periodDue   = $periodPurchases->sum('due');
    $periodPaid  = $periodTotal - $periodDue;
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="mini-stat"><span>Purchases (period)</span><strong>{{ $periodPurchases->count() }}</strong></div>
    <div class="mini-stat"><span>Total Purchased</span><strong style="white-space:nowrap;">{{ $appCurrency }} {{ number_format($periodTotal, 2) }}</strong></div>
    <div class="mini-stat"><span>Paid</span><strong style="white-space:nowrap;">{{ $appCurrency }} {{ number_format($periodPaid, 2) }}</strong></div>
    <div class="mini-stat"><span>Owed to Suppliers (all time)</span><strong style="color:var(--danger); white-space:nowrap;">{{ $appCurrency }} {{ number_format($purchases['payable_all'], 2) }}</strong></div>
</div>
@php
    $periodPurchases = $recentPurchases->filter(function ($r) use ($from, $to) {
        $date = \Illuminate\Support\Carbon::parse($r->purchase->order_date ?? $r->purchase->created_at);
        return $date->between($from->copy()->startOfDay(), $to->copy()->endOfDay());
    });
@endphp

        <div class="text-xs font-semibold mb-2" style="color:var(--ink-soft);">Recent purchases</div>
    <div class="overflow-x-auto mb-4">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th class="num">Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($periodPurchases as $r)
                    <tr>
                        <td>
                            <a href="{{ route('purchases.show', $r->purchase) }}" class="font-medium" style="color:var(--accent-ink);" title="View purchase">#{{ $r->purchase->id }}</a>
                        </td>
                        <td class="font-medium">{{ $r->name }}</td>
                        <td style="color:var(--ink-soft);">{{ $r->purchase->item_type === 'filament' ? 'Filament' : 'Supply' }}</td>
                        <td style="color:var(--ink-soft);">{{ $r->purchase->supplier->name ?? 'No supplier' }}</td>
                        <td style="color:var(--ink-soft); white-space:nowrap;">{{ \Illuminate\Support\Carbon::parse($r->purchase->order_date ?? $r->purchase->created_at)->format('Y-m-d') }}</td>
                        <td class="num font-medium" style="white-space:nowrap;">{{ $appCurrency }} {{ number_format($r->total, 2) }}</td>
                        <td>
                            @if ($r->due > 0.005)
                                <span class="status-badge status-cancelled">Due</span>
                            @else
                                <span class="status-badge status-completed">Paid</span>
                            @endif
                        </td>
                    </tr>
                @empty
                   <tr><td colspan="7" class="text-center" style="color:var(--ink-soft);">No purchases in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-2 gap-5">
        <div>
            <div class="text-xs font-semibold mb-2" style="color:var(--ink-soft);">By supplier (period)</div>
            @forelse ($purchases['by_supplier']->take(5) as $name => $amount)
                <div class="flex justify-between text-xs mb-1">
                    <span>{{ $name }}</span>
                    <span class="num">{{ $appCurrency }} {{ number_format($amount, 2) }}</span>
                </div>
            @empty
                <p class="text-xs" style="color:var(--ink-soft);">No purchases in this period.</p>
            @endforelse
        </div>
        <div>
            <div class="text-xs font-semibold mb-2" style="color:var(--ink-soft);">Breakdown (period)</div>
            <div class="flex justify-between text-xs mb-1"><span>Filament</span><span class="num">{{ $appCurrency }} {{ number_format($purchases['filament_total'], 2) }}</span></div>
            <div class="flex justify-between text-xs mb-1"><span>Supplies</span><span class="num">{{ $appCurrency }} {{ number_format($purchases['supplies_total'], 2) }}</span></div>
            <div class="flex justify-between text-xs"><span>Shipping</span><span class="num">{{ $appCurrency }} {{ number_format($purchases['shipping'], 2) }}</span></div>
        </div>
    </div>
</div>