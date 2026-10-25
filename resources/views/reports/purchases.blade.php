@php($appCurrency = \App\Models\Setting::get('currency', 'AF'))
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <a href="{{ route('reports.index') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to reports</a>
        <h1 class="ledger-font text-3xl mt-2 mb-1">Purchase Report</h1>

        {{-- Everything in here is swapped (no page reload) when a date button is clicked --}}
        <div data-live-results>
            <p class="text-sm mb-4" style="color:var(--ink-soft);">
                Stock and supply purchases, newest first (not counted as expenses; cancelled ones excluded) &middot;
                @if ($isAll)
                    All purchases
                @else
                    {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
                @endif
            </p>

            <div class="flex items-center gap-4 flex-wrap mb-6">
                <div class="pill-group">
                    @foreach (['all' => 'All', 'today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'] as $key => $label)
                        <a href="{{ route('reports.purchases', ['range' => $key]) }}"
                            class="pill {{ $range === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('reports.purchases.exportPdf', request()->query()) }}"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="border:1px solid var(--line); color:var(--ink-soft);">Export PDF</a>
                    <a href="{{ route('reports.purchases.exportExcel', request()->query()) }}"
                        class="text-sm font-semibold px-4 py-2 rounded-md"
                        style="border:1px solid var(--line); color:var(--ink-soft);">Export Excel</a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="card-3d p-4">
                    <div class="text-xs" style="color:var(--ink-soft);">Purchases</div>
                    <div class="text-xl font-bold num mt-0.5">{{ $purchases['count'] }}</div>
                </div>
                <div class="card-3d p-4">
                    <div class="text-xs" style="color:var(--ink-soft);">Total Purchased</div>
                    <div class="text-xl font-bold num mt-0.5" style="white-space:nowrap;">{{ $appCurrency }} {{ number_format($purchases['total'], 2) }}</div>
                </div>
                <div class="card-3d p-4">
                    <div class="text-xs" style="color:var(--ink-soft);">Paid</div>
                    <div class="text-xl font-bold num mt-0.5" style="color:var(--success); white-space:nowrap;">{{ $appCurrency }} {{ number_format($purchases['paid'], 2) }}</div>
                </div>
                <div class="card-3d p-4">
                    <div class="text-xs" style="color:var(--ink-soft);">Owed to Suppliers</div>
                    <div class="text-xl font-bold num mt-0.5" style="color:var(--danger); white-space:nowrap;">{{ $appCurrency }} {{ number_format($purchases['payable'], 2) }}</div>
                </div>
            </div>

            <div class="card-3d overflow-hidden">
                <div style="overflow-x:auto;">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">#</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Item</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Type</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Supplier</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Qty</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Total</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Paid</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Due</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases['rows'] as $r)
                            @php
                                $p  = $r->purchase;
                                $st = $p->status ?: 'pending';
                                $unit = $p->item_type === 'filament' ? 'g' : ($p->unit ?: ($p->supply->unit ?? ''));
                            @endphp
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-3">
                                    <a href="{{ route('purchases.show', $p) }}" class="font-medium" style="color:var(--accent-ink);" title="View purchase">#{{ $p->id }}</a>
                                </td>
                                <td class="px-4 py-3" style="color:var(--ink-soft); white-space:nowrap;">{{ \Illuminate\Support\Carbon::parse($p->order_date ?? $p->created_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">{{ $r->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge" style="{{ $p->item_type === 'filament' ? 'background:var(--accent-soft); color:var(--accent-ink);' : 'background:var(--line); color:var(--ink-soft);' }}">
                                        {{ $p->item_type === 'filament' ? 'Filament' : 'Supply' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $p->supplier->name ?? 'â€”' }}</td>
                                <td class="px-4 py-3 text-right num" style="white-space:nowrap;">{{ rtrim(rtrim(number_format((float) $p->quantity, 2), '0'), '.') }} {{ $unit }}</td>
                                <td class="px-4 py-3 text-right num font-medium" style="white-space:nowrap;">{{ number_format($r->total, 2) }}</td>
                                <td class="px-4 py-3 text-right num" style="white-space:nowrap;">{{ number_format($r->paid, 2) }}</td>
                                <td class="px-4 py-3 text-right num" style="white-space:nowrap; color:{{ $r->due > 0.005 ? 'var(--danger)' : 'var(--ink-soft)' }};">{{ number_format($r->due, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge" style="background:{{ $st === 'arrived' ? 'var(--success-soft)' : 'var(--warn-soft)' }}; color:{{ $st === 'arrived' ? 'var(--success)' : 'var(--warn)' }};">
                                        {{ ucfirst($st) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                    No purchases in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.pixel>