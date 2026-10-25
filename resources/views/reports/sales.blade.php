<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <a href="{{ Route::has('reports.index') ? route('reports.index') : url()->previous() }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to reports
        </a>

        <h1 class="ledger-font text-3xl mb-6">Sales Report</h1>

        {{-- Range pills + custom dates (stay in place; the pills switch instantly) --}}
        <form method="GET" class="flex flex-wrap items-center gap-2 mb-6">
            <div class="pill-group" data-live-tabs="range">
                @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'] as $key => $label)
                    <a href="{{ route('reports.sales', ['range' => $key]) }}"
                        class="pill {{ request('range', 'this_month') === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <input type="date" name="from" value="{{ request('from') }}" class="field">
            <span class="text-sm" style="color:var(--ink-soft);">to</span>
            <input type="date" name="to" value="{{ request('to') }}" class="field">
            <input type="hidden" name="range" value="custom">
            <button class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                Apply
            </button>
        </form>

        {{-- Everything below refreshes when a pill is clicked --}}
        <div data-live-results>
            <p class="text-sm mb-4" style="color:var(--ink-soft);">
                {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
            </p>

            <div class="flex gap-2 mb-6">
                <a href="{{ route('reports.sales.exportPdf', request()->query()) }}"
                    class="text-sm font-semibold px-4 py-2 rounded-md"
                    style="border:1px solid var(--line); color:var(--ink-soft);">
                    Export PDF
                </a>
                <a href="{{ route('reports.sales.exportExcel', request()->query()) }}"
                    class="text-sm font-semibold px-4 py-2 rounded-md"
                    style="border:1px solid var(--line); color:var(--ink-soft);">
                    Export Excel
                </a>
            </div>

            <div class="card-3d overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Invoice</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                            <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Customer</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Total</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Paid</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Balance</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Cost</th>
                            <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-3 font-medium">{{ $sale->invoice_number }}</td>
                                <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $sale->sold_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                <td class="px-4 py-3 text-right num">{{ number_format($sale->total, 2) }}</td>
                                <td class="px-4 py-3 text-right num">{{ number_format($sale->amount_paid, 2) }}</td>
                                <td class="px-4 py-3 text-right num" style="color:{{ $sale->balance > 0 ? 'var(--danger)' : 'var(--ink-soft)' }};">
                                    {{ number_format($sale->balance, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">{{ number_format($sale->cost_total, 2) }}</td>
                                <td class="px-4 py-3 text-right num font-medium" style="color:{{ $sale->gross_profit >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                    {{ number_format($sale->gross_profit, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                    No sales in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</x-layouts.pixel>