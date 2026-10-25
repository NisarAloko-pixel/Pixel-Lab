@php($appCurrency = \App\Models\Setting::get('currency', 'AF'))
<x-layouts.pixel>
{{-- Chart.js loads once, outside the live area, so it is ready before the chart script runs --}}
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>

<div style="background:var(--paper); color:var(--ink);">
    {{-- Everything inside data-live-results is swapped when a pill is clicked --}}
    <div class="max-w-7xl mx-auto px-6 py-10" id="report-print-area" data-live-results>

        {{-- ============================================================
             1. HEADER + GLOBAL FILTERS
        ============================================================= --}}
        <div class="flex justify-between items-end mb-6 flex-wrap gap-4">
            <div>
                <h1 class="ledger-font text-3xl mb-1">Reports &amp; Analytics</h1>
                <p class="text-sm" style="color:var(--ink-soft);">
                    Track your business performance and financial health &middot;
                    {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
                </p>
            </div>
            <div class="flex items-center gap-4 flex-wrap">
                <div class="pill-group">
                    @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year', 'custom' => 'Custom'] as $key => $label)
                        <a href="{{ route('reports.index', array_merge(request()->query(), ['range' => $key])) }}"
                            class="pill {{ request('range', 'this_month') === $key ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('reports.index.exportPdf', request()->query()) }}" class="export-btn">PDF</a>
                    <a href="{{ route('reports.index.exportExcel', request()->query()) }}" class="export-btn">Excel</a>
                    <button type="button" onclick="window.print()" class="export-btn">Print</button>
                </div>
            </div>
        </div>

        {{--
            NOTE: Section 13's global filters (customer/product/category/
            payment status/sale status) aren't wired in here — buildIndexData()
            and FinancialService::summary() only take a date range right now.
            Adding them means threading a $filters array through summary(),
            productProfitReport(), and every private helper below, which I've
            left out until I can see FinancialService's signature. The date
            range selector above already round-trips through resolveRange().
        --}}

        {{-- ============================================================
             2. MAIN KPI CARDS
        ============================================================= --}}
      <div class="kpi-grid mb-6">
    @php
        $fmtPct = function (?float $v) {
            if ($v === null) return 'new';
            return ($v >= 0 ? '+' : '') . number_format($v, 1) . '%';
        };
    @endphp

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Total Sales</div>
        <div class="kpi-value num">{{ $appCurrency }} {{ number_format($salesTotals->gross_sales, 2) }}</div>
        <div class="text-xs mt-1" style="color:var(--ink-soft);">{{ $salesAnalysis['total_orders'] }} orders</div>
    </div>

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Total Revenue</div>
        <div class="kpi-value num">{{ $appCurrency }} {{ number_format($summary['revenue'], 2) }}</div>
        <div class="text-xs mt-1 trend-{{ ($comparisons['revenue'] ?? 0) >= 0 ? 'up' : 'down' }}">{{ $fmtPct($comparisons['revenue']) }} vs prior</div>
    </div>

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Net Profit</div>
        <div class="kpi-value num" style="color:{{ $summary['net_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ $appCurrency }} {{ number_format($summary['net_profit'], 2) }}</div>
        <div class="text-xs mt-1" style="color:var(--ink-soft);">{{ number_format($summary['profit_margin_pct'], 1) }}% margin</div>
    </div>

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Total Expenses</div>
        <div class="kpi-value num">{{ $appCurrency }} {{ number_format($summary['cogs'] + $summary['operating_expenses'], 2) }}</div>
        <div class="text-xs mt-1 trend-{{ ($comparisons['expenses'] ?? 0) <= 0 ? 'up' : 'down' }}">{{ $fmtPct($comparisons['expenses']) }} vs prior</div>
    </div>

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Total Purchases</div>
        <div class="kpi-value num">{{ $appCurrency }} {{ number_format($purchases['total'], 2) }}</div>
        <div class="text-xs mt-1" style="color:var(--ink-soft);">{{ $purchases['count'] }} purchases</div>
    </div>

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Receivables</div>
        <div class="kpi-value num">{{ $appCurrency }} {{ number_format($totalReceivables, 2) }}</div>
        <div class="text-xs mt-1" style="color:var(--ink-soft);">{{ $customerAnalysis['with_balance'] }} customers owing</div>
    </div>

    <div class="metric-card">
        <div class="text-xs" style="color:var(--ink-soft);">Amount Collected</div>
        <div class="kpi-value num">{{ $appCurrency }} {{ number_format($payments['total_paid'], 2) }}</div>
        <div class="text-xs mt-1" style="color:var(--ink-soft);">{{ number_format($payments['collection_pct'], 1) }}% collected</div>
    </div>
</div>

        {{-- ============================================================
             3. SALES & PROFIT OVERVIEW
        ============================================================= --}}
        <div class="card-3d p-6 mb-6">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
                <h2 class="ledger-font text-lg">Sales &amp; profit overview</h2>
                <div class="pill-group">
                    @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $key => $label)
                        <a href="{{ route('reports.index', array_merge(request()->query(), ['granularity' => $key])) }}"
                            class="pill {{ $granularity === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div style="height:300px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- ============================================================
             4. FINANCIAL SUMMARY  +  6. EXPENSE ANALYSIS
        ============================================================= --}}
        <div class="grid grid-cols-3 gap-5 mb-6">
            <div class="card-3d p-6 col-span-2">
                <h2 class="ledger-font text-lg mb-4">Financial summary</h2>
                <table class="summary-table">
                    <tbody>
                        <tr><td>Gross Sales</td><td class="num">{{ $appCurrency }} {{ number_format($salesTotals->gross_sales, 2) }}</td></tr>
                        <tr><td>Discounts</td><td class="num">{{ $appCurrency }} {{ number_format($salesTotals->discounts, 2) }}</td></tr>
                        <tr><td>Tax</td><td class="num">{{ $appCurrency }} {{ number_format($salesTotals->tax, 2) }}</td></tr>
                        <tr class="subtotal-row"><td>Net Revenue</td><td class="num">{{ $appCurrency }} {{ number_format($summary['revenue'], 2) }}</td></tr>
                        <tr><td>Product/Printing Cost</td><td class="num">{{ $appCurrency }} {{ number_format($summary['cogs'], 2) }}</td></tr>
                        <tr><td>Operating Expenses</td><td class="num">{{ $appCurrency }} {{ number_format($summary['operating_expenses'], 2) }}</td></tr>
                        <tr class="subtotal-row"><td>Net Profit</td><td class="num" style="color:{{ $summary['net_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ $appCurrency }} {{ number_format($summary['net_profit'], 2) }}</td></tr>
                        <tr><td>Profit Margin</td><td class="num">{{ number_format($summary['profit_margin_pct'], 1) }}%</td></tr>
                        <tr><td>Amount Paid</td><td class="num">{{ $appCurrency }} {{ number_format($payments['total_paid'], 2) }}</td></tr>
                        <tr><td>Outstanding Balance</td><td class="num">{{ $appCurrency }} {{ number_format($payments['total_outstanding'], 2) }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card-3d p-6">
                <h2 class="ledger-font text-lg mb-4">Expense breakdown</h2>
                <div style="height:160px;" class="mb-4">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="space-y-2 mb-3">
                    @forelse ($expenseBreakdown as $category => $amount)
                        <div class="flex justify-between text-xs">
                            <span style="color:var(--ink-soft);">{{ $category }}</span>
                            <span class="num font-medium">{{ $appCurrency }} {{ number_format($amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-xs" style="color:var(--ink-soft);">No expenses in this period.</p>
                    @endforelse
                </div>
                @if ($expenseBreakdown->isNotEmpty())
                    <div class="text-xs pt-2" style="border-top:1px solid var(--line); color:var(--ink-soft);">
                        Highest category: <strong style="color:var(--ink);">{{ $expenseBreakdown->keys()->first() }}</strong><br>
                        {{ $summary['revenue'] > 0 ? number_format(($summary['operating_expenses'] / $summary['revenue']) * 100, 1) : '0.0' }}% of revenue
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             5. SALES ANALYSIS  +  7. PROFIT ANALYSIS
        ============================================================= --}}
        <div class="grid grid-cols-2 gap-5 mb-6">
            <div class="card-3d p-6">
                <h2 class="ledger-font text-lg mb-4">Sales analysis</h2>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="mini-stat"><span>Total Orders</span><strong>{{ $salesAnalysis['total_orders'] }}</strong></div>
                    <div class="mini-stat"><span>Completed</span><strong>{{ $salesAnalysis['completed_orders'] }}</strong></div>
                    <div class="mini-stat"><span>Pending</span><strong>{{ $salesAnalysis['pending_orders'] }}</strong></div>
                    <div class="mini-stat"><span>Cancelled</span><strong>{{ $salesAnalysis['cancelled_orders'] }}</strong></div>
                    <div class="mini-stat"><span>Avg Order Value</span><strong>{{ $appCurrency }} {{ number_format($salesAnalysis['average_order_value'], 2) }}</strong></div>
                    <div class="mini-stat"><span>Items Sold</span><strong>{{ $salesAnalysis['total_items_sold'] }}</strong></div>
                </div>
                <div class="text-xs" style="color:var(--ink-soft);">
                    Best-selling product: <strong style="color:var(--ink);">{{ $salesAnalysis['best_selling_product'] ?? '—' }}</strong><br>
                    Best-performing category: <strong style="color:var(--ink);">{{ $salesAnalysis['best_performing_category'] ?? '—' }}</strong>
                </div>
            </div>
            <div class="card-3d p-6">
                <h2 class="ledger-font text-lg mb-4">Profit analysis</h2>
                <div class="funnel mb-4">
                    @php
                        $funnelMax = max($profitAnalysis['revenue'], $purchases['total'], 1);
                        $steps = [
                            ['Revenue',    $profitAnalysis['revenue'],    'var(--accent)'],
                            ['Cost',       $profitAnalysis['cost'],       'var(--warn)'],
                            ['Expenses',   $profitAnalysis['expenses'],   'var(--danger)'],
                            ['Net Profit', $profitAnalysis['net_profit'], 'var(--success)'],
                            ['Purchases',  $purchases['total'],           '#3457A6'],
                        ];
                    @endphp
                    @foreach ($steps as [$label, $value, $color])
                        <div class="funnel-row">
                            <span class="funnel-label">{{ $label }}</span>
                            <div class="funnel-track">
                                <div class="funnel-fill" style="width: {{ max(4, (abs($value) / $funnelMax) * 100) }}%; background:{{ $color }};"></div>
                            </div>
                            <span class="num funnel-value">{{ $appCurrency }} {{ number_format($value, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="text-xs" style="color:var(--ink-soft);">
                    Profit margin: <strong style="color:var(--ink);">{{ number_format($profitAnalysis['profit_margin_pct'], 1) }}%</strong><br>
                    <span>Purchases are stock bought in this period. Shown for reference, not deducted from net profit.</span>
                </div>
            </div>
        </div>

        {{-- ============================================================
             8. TOP PRODUCTS  +  9. CUSTOMER ANALYSIS
        ============================================================= --}}
        <div class="grid grid-cols-2 gap-5 mb-6">
            <div class="card-3d p-6">
                <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                    <h2 class="ledger-font text-lg">Top products</h2>
                    <div class="pill-group">
                        @foreach (['profit' => 'Profit', 'revenue' => 'Revenue', 'units' => 'Most Sold'] as $key => $label)
                            <a href="{{ route('reports.index', array_merge(request()->query(), ['top_products_by' => $key])) }}"
                                class="pill {{ $topProductsBy === $key ? 'active' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr><th>Product</th><th>Category</th><th class="num">Units</th><th class="num">Revenue</th><th class="num">Profit</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($topProducts as $p)
                            <tr>
                                <td>{{ $p->name }}</td>
                                <td style="color:var(--ink-soft);">{{ $p->category }}</td>
                                <td class="num">{{ (int) $p->quantity_sold }}</td>
                                <td class="num">{{ $appCurrency }} {{ number_format($p->revenue, 2) }}</td>
                                <td class="num" style="color:var(--success);">{{ $appCurrency }} {{ number_format($p->profit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center" style="color:var(--ink-soft);">No product sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-3d p-6">
                <h2 class="ledger-font text-lg mb-4">Customer analysis</h2>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="mini-stat"><span>Total Customers</span><strong>{{ $customerAnalysis['total_customers'] }}</strong></div>
                    <div class="mini-stat"><span>New</span><strong>{{ $customerAnalysis['new_customers'] }}</strong></div>
                    <div class="mini-stat"><span>Returning</span><strong>{{ $customerAnalysis['returning_customers'] }}</strong></div>
                    <div class="mini-stat"><span>With Balance</span><strong>{{ $customerAnalysis['with_balance'] }}</strong></div>
                </div>
                <div class="text-xs font-semibold mb-2" style="color:var(--ink-soft);">Top customers by sales</div>
                <div class="space-y-1 mb-3">
                    @forelse ($customerAnalysis['top_by_sales'] as $c)
                        <div class="flex justify-between text-xs">
                            <span>{{ $c->name }}</span>
                            <span class="num">{{ $appCurrency }} {{ number_format($c->total_sales, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-xs" style="color:var(--ink-soft);">No customer data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================
             10. INVENTORY  +  11. PAYMENTS & RECEIVABLES
        ============================================================= --}}
        <div class="grid grid-cols-2 gap-5 mb-6">
            <div class="card-3d p-6">
                <h2 class="ledger-font text-lg mb-4">Inventory report</h2>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="mini-stat"><span>Total Products</span><strong>{{ $inventory['total_products'] }}</strong></div>
                    <div class="mini-stat"><span>Inventory Value</span><strong>{{ $appCurrency }} {{ number_format($inventory['total_inventory_value'], 2) }}</strong></div>
                    <div class="mini-stat"><span>Inventory Cost</span><strong>{{ $appCurrency }} {{ number_format($inventory['total_inventory_cost'], 2) }}</strong></div>
                    <div class="mini-stat"><span>Low Stock</span><strong style="color:var(--warn);">{{ $inventory['low_stock_count'] }}</strong></div>
                </div>
                @if ($inventory['out_of_stock_count'] > 0 || $inventory['low_stock_count'] > 0)
                    <div class="text-xs font-semibold mb-2" style="color:var(--ink-soft);">Needs attention</div>
                    <div class="space-y-1">
                        @foreach ($inventory['out_of_stock_products'] as $p)
                            <div class="flex justify-between text-xs stock-alert stock-out">
                                <span>{{ $p->name }} ({{ $p->sku }})</span>
                                <span>Out of stock</span>
                            </div>
                        @endforeach
                        @foreach ($inventory['low_stock_products'] as $p)
                            <div class="flex justify-between text-xs stock-alert stock-low">
                                <span>{{ $p->name }} ({{ $p->sku }})</span>
                                <span>{{ $p->current_stock }} left &middot; min {{ $p->min_stock_level }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs" style="color:var(--ink-soft);">All products are adequately stocked.</p>
                @endif
            </div>

            <div class="card-3d p-6">
                <h2 class="ledger-font text-lg mb-4">Payments &amp; receivables</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div style="height:150px;" class="col-span-1">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <div class="col-span-2 space-y-2">
                        <div class="flex justify-between text-xs"><span style="color:var(--ink-soft);">Total Sales</span><span class="num font-medium">{{ $appCurrency }} {{ number_format($payments['total_sales'], 2) }}</span></div>
                        <div class="flex justify-between text-xs"><span style="color:var(--ink-soft);">Amount Paid</span><span class="num font-medium">{{ $appCurrency }} {{ number_format($payments['total_paid'], 2) }}</span></div>
                        <div class="flex justify-between text-xs"><span style="color:var(--ink-soft);">Outstanding</span><span class="num font-medium">{{ $appCurrency }} {{ number_format($payments['total_outstanding'], 2) }}</span></div>
                        <div class="pt-2 mt-2" style="border-top:1px solid var(--line);">
                            <div class="flex justify-between text-xs"><span style="color:var(--success);">Fully Paid</span><span class="num">{{ $payments['fully_paid_orders'] }}</span></div>
                            <div class="flex justify-between text-xs"><span style="color:var(--warn);">Partially Paid</span><span class="num">{{ $payments['partially_paid_orders'] }}</span></div>
                            <div class="flex justify-between text-xs"><span style="color:var(--danger);">Unpaid</span><span class="num">{{ $payments['unpaid_orders'] }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


                @include('reports.purchases-summary')

        {{-- ============================================================
             12. RECENT TRANSACTIONS
        ============================================================= --}}
        <div class="card-3d p-6 mb-6">
            <h2 class="ledger-font text-lg mb-4">Recent transactions</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th><th>Customer</th><th>Date</th>
                        <th class="num">Total</th><th class="num">Paid</th><th class="num">Balance</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $t)
                        <tr>
                            <td>{{ $t->invoice_number }}</td>
                            <td>{{ $t->customer->name ?? '—' }}</td>
                            <td style="color:var(--ink-soft);">{{ \Illuminate\Support\Carbon::parse($t->sold_at)->format('Y-m-d') }}</td>
                            <td class="num">{{ $appCurrency }} {{ number_format($t->total, 2) }}</td>
                            <td class="num">{{ $appCurrency }} {{ number_format($t->amount_paid, 2) }}</td>
                            <td class="num">{{ $appCurrency }} {{ number_format($t->balance, 2) }}</td>
                            <td><span class="status-badge status-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center" style="color:var(--ink-soft);">No transactions in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Quick links to detailed reports --}}
                <div class="grid grid-cols-4 gap-4">
            <a href="{{ route('reports.sales') }}" class="link-card">
                <div><div class="font-semibold text-sm">Sales report</div><div class="text-xs mt-0.5" style="color:var(--ink-soft);">Every invoice, itemized</div></div>
                <span style="color:var(--accent-ink);">&rarr;</span>
            </a>
            <a href="{{ route('reports.product-profit') }}" class="link-card">
                <div><div class="font-semibold text-sm">Product profit</div><div class="text-xs mt-0.5" style="color:var(--ink-soft);">Margin by product</div></div>
                <span style="color:var(--accent-ink);">&rarr;</span>
            </a>
            <a href="{{ route('reports.expenses') }}" class="link-card">
                <div><div class="font-semibold text-sm">Expense report</div><div class="text-xs mt-0.5" style="color:var(--ink-soft);">Every expense logged</div></div>
                <span style="color:var(--accent-ink);">&rarr;</span>
            </a>
                        <a href="{{ route('reports.purchases') }}" class="link-card">
                <div><div class="font-semibold text-sm">Purchase report</div><div class="text-xs mt-0.5" style="color:var(--ink-soft);">Stock and supply orders</div></div>
                <span style="color:var(--accent-ink);">&rarr;</span>
            </a>
        </div>

        {{-- Charts: this script is inside the live area, so it re-runs after every pill click --}}
        <script>
        (function () {
            new Chart(document.getElementById('trendChart'), {
                type: 'bar',
                data: {
                    labels: @json(collect($trend)->pluck('label')),
                    datasets: [
                        { label: 'Revenue', data: @json(collect($trend)->pluck('revenue')), backgroundColor: '#A8571E', borderRadius: 6, maxBarThickness: 26 },
                        { label: 'Expenses', data: @json(collect($trend)->pluck('expenses')), backgroundColor: '#C9BBA3', borderRadius: 6, maxBarThickness: 26 },
                        { label: 'Net Profit', data: @json(collect($trend)->pluck('net_profit')), type: 'line', borderColor: '#3D6B4F', backgroundColor: '#3D6B4F', tension: .3 },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                    scales: { x: { grid: { display: false } }, y: { grid: { color: '#E3DFD3' }, beginAtZero: true } },
                },
            });

            new Chart(document.getElementById('expenseChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($expenseBreakdown->keys()),
                    datasets: [{ data: @json($expenseBreakdown->values()), backgroundColor: ['#A8571E', '#3D6B4F', '#9A7B2E', '#3457A6', '#A13D2E', '#6B7080'], borderWidth: 2, borderColor: '#FFFFFF' }],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '65%' },
            });

            new Chart(document.getElementById('paymentChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Fully Paid', 'Partially Paid', 'Unpaid'],
                    datasets: [{
                        data: [{{ $payments['fully_paid_orders'] }}, {{ $payments['partially_paid_orders'] }}, {{ $payments['unpaid_orders'] }}],
                        backgroundColor: ['#3D6B4F', '#9A7B2E', '#A13D2E'],
                        borderWidth: 2, borderColor: '#FFFFFF',
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '60%' },
            });
        })();
        </script>
    </div>
</div>

<style>
    .export-btn { font-size:.8rem; font-weight:600; padding:.5rem 1rem; border-radius:.375rem; border:1px solid var(--line); color:var(--ink-soft); }
    .export-btn:hover { color:var(--ink); border-color:var(--ink-soft); }
    .filter-bar { display:flex; gap:1rem; align-items:end; flex-wrap:wrap; }
    .filter-field { display:flex; flex-direction:column; gap:.25rem; min-width:150px; }
    .filter-field label { font-size:.7rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.03em; }
    .filter-field select { font-size:.8rem; padding:.4rem .6rem; border:1px solid var(--line); border-radius:.375rem; background:var(--paper); color:var(--ink); }
    .trend-up { color:var(--success); }
    .trend-down { color:var(--danger); }
    .summary-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .summary-table td { padding:.5rem .25rem; border-bottom:1px solid var(--line); }
    .summary-table td:first-child { color:var(--ink-soft); }
    .summary-table td:last-child { text-align:right; font-weight:600; }
    .summary-table .subtotal-row td { font-weight:700; color:var(--ink); border-top:2px solid var(--line); }
    .mini-stat { background:var(--paper-soft, rgba(0,0,0,.02)); border:1px solid var(--line); border-radius:.5rem; padding:.6rem .75rem; display:flex; flex-direction:column; gap:.15rem; }
    .mini-stat span { font-size:.68rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.02em; }
    .mini-stat strong { font-size:1rem; }
    .funnel-row { display:flex; align-items:center; gap:.6rem; margin-bottom:.6rem; }
    .funnel-label { width:80px; font-size:.75rem; color:var(--ink-soft); }
    .funnel-track { flex:1; height:10px; background:var(--line); border-radius:999px; overflow:hidden; }
    .funnel-fill { height:100%; border-radius:999px; }
    .funnel-value { width:110px; text-align:right; font-size:.8rem; }
    .data-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .data-table th { text-align:left; font-size:.68rem; text-transform:uppercase; letter-spacing:.03em; color:var(--ink-soft); padding:.5rem .4rem; border-bottom:2px solid var(--line); }
    .data-table td { padding:.55rem .4rem; border-bottom:1px solid var(--line); }
    .data-table th.num, .data-table td.num { text-align:right; }
    .stock-alert { padding:.35rem .5rem; border-radius:.375rem; }
    .stock-alert.stock-out { background:var(--danger-soft); color:var(--danger); }
    .stock-alert.stock-low { background:var(--warn-soft); color:var(--warn); }
    .status-badge { font-size:.7rem; font-weight:600; padding:.15rem .55rem; border-radius:999px; }
    .status-completed { background:var(--success-soft); color:var(--success); }
    .status-pending { background:var(--warn-soft); color:var(--warn); }
    .status-cancelled { background:var(--danger-soft); color:var(--danger); }

    /* Print: hide the app chrome (sidebar/topbar from x-layouts.pixel) and
       show only the report itself. Hiding everything by default and
       un-hiding just #report-print-area avoids needing to know the
       layout's actual sidebar/header selectors. */
    @media print {
        body * { visibility: hidden; }
        #report-print-area, #report-print-area * { visibility: visible; }
        #report-print-area {
            position: absolute; left: 0; top: 0; width: 100%;
            max-width: none; margin: 0; padding: 0;
        }
        .export-btn, .pill-group, form.filter-bar { display: none !important; }
    }

    .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; }
.kpi-grid .metric-card { padding:.9rem 1rem; min-width:0; }
.kpi-grid .kpi-value { font-size:1rem; font-weight:700; white-space:nowrap; margin-top:.15rem; }
</style>
</x-layouts.pixel>