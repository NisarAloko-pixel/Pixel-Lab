<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">
        <a href="{{ Route::has('reports.index') ? route('reports.index') : url()->previous() }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to reports
        </a>

        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div class="flex items-center gap-4 flex-wrap">
    <div class="pill-group">
        @foreach (['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'] as $key => $label)
            <a href="{{ route('reports.profit', ['range' => $key]) }}"
                class="pill {{ request('range', 'this_month') === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.profit.exportPdf', request()->query()) }}"
            class="text-sm font-semibold px-4 py-2 rounded-md"
            style="border:1px solid var(--line); color:var(--ink-soft);">
            Export PDF
        </a>
        <a href="{{ route('reports.profit.exportExcel', request()->query()) }}"
            class="text-sm font-semibold px-4 py-2 rounded-md"
            style="border:1px solid var(--line); color:var(--ink-soft);">
            Export Excel
        </a>
    </div>
</div>    
        

        {{-- Top metric cards: revenue, gross profit, net profit, margin â€” each with its own vs-prior badge --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @php
                $cards = [
                    ['label' => 'Revenue', 'value' => $summary['revenue'], 'pct' => $comparison['revenue'], 'color' => 'blue'],
                    ['label' => 'Gross Profit', 'value' => $summary['gross_profit'], 'pct' => $comparison['gross_profit'], 'color' => 'accent'],
                    ['label' => 'Net Profit', 'value' => $summary['net_profit'], 'pct' => $comparison['net_profit'], 'color' => 'success'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="metric-card">
                    <div class="text-xs mb-2" style="color:var(--ink-soft);">{{ $card['label'] }}</div>
                    <div class="num text-2xl font-bold mb-2" style="color:var(--{{ $card['color'] }}-ink, var(--ink));">
                        {{ number_format($card['value'], 2) }}
                    </div>
                    @if (is_null($card['pct']))
                        <span class="text-xs" style="color:var(--ink-soft);">No prior data</span>
                    @else
                        <span class="text-xs font-semibold {{ $card['pct'] >= 0 ? 'change-up' : 'change-down' }}">
                            {{ $card['pct'] >= 0 ? 'â–²' : 'â–¼' }} {{ number_format(abs($card['pct']), 1) }}% vs prior
                        </span>
                    @endif
                </div>
            @endforeach

            <div class="metric-card">
                <div class="text-xs mb-2" style="color:var(--ink-soft);">Profit Margin</div>
                <div class="num text-2xl font-bold mb-2">{{ number_format($summary['profit_margin_pct'], 1) }}%</div>
                <span class="text-xs font-semibold {{ $comparison['profit_margin_pct'] >= 0 ? 'change-up' : 'change-down' }}">
                    {{ $comparison['profit_margin_pct'] >= 0 ? 'â–²' : 'â–¼' }} {{ number_format(abs($comparison['profit_margin_pct']), 1) }} pts vs prior
                </span>
            </div>
        </div>

        {{-- Chart + cost breakdown side by side --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="card-3d p-6 lg:col-span-2">
                <h2 class="font-semibold text-sm mb-4" style="color:var(--ink-soft);">Last 6 months</h2>
                <canvas id="profitTrendChart" height="200"></canvas>
            </div>

            <div class="card-3d p-6">
                <h2 class="font-semibold text-sm mb-4" style="color:var(--ink-soft);">Cost breakdown</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color:var(--ink-soft);">COGS</span>
                        <span class="num font-medium" style="color:var(--danger);">-{{ number_format($summary['cogs'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm" style="color:var(--ink-soft);">Operating Expenses</span>
                        <span class="num font-medium" style="color:var(--danger);">-{{ number_format($summary['operating_expenses'], 2) }}</span>
                    </div>
                    <div class="pt-3" style="border-top:1px dashed var(--line);">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold">Net Profit</span>
                            <span class="num value-strong" style="color:{{ $summary['net_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                {{ number_format($summary['net_profit'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6 pt-5" style="border-top:1px dashed var(--line);">
                    <div>
                        <div class="text-xs mb-1" style="color:var(--ink-soft);">Sales Count</div>
                        <div class="font-semibold num">{{ $summary['sales_count'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs mb-1" style="color:var(--ink-soft);">Avg Order Value</div>
                        <div class="font-semibold num">{{ number_format($summary['average_order_value'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('profitTrendChart');
    const style = getComputedStyle(document.documentElement);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($months->pluck('label')),
            datasets: [
                {
                    label: 'Revenue',
                    data: @json($months->pluck('revenue')),
                    backgroundColor: style.getPropertyValue('--accent-soft').trim(),
                    borderColor: style.getPropertyValue('--accent-ink').trim(),
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Net Profit',
                    type: 'line',
                    data: @json($months->pluck('net_profit')),
                    borderColor: style.getPropertyValue('--success').trim(),
                    backgroundColor: style.getPropertyValue('--success').trim(),
                    tension: 0.3,
                    pointRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: style.getPropertyValue('--ink').trim() } }
            },
            scales: {
                x: { ticks: { color: style.getPropertyValue('--ink-soft').trim() }, grid: { display: false } },
                y: { ticks: { color: style.getPropertyValue('--ink-soft').trim() }, grid: { color: style.getPropertyValue('--line').trim() } }
            }
        }
    });
});
</script>
</x-layouts.pixel>
