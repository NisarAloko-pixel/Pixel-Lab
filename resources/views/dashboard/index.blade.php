<x-layouts.pixel>
@php
    /* ---------- helpers ---------- */
    // Links fall back to "#" if a route name doesn't exist yet, so the page never breaks.
    $r = fn ($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : '#';
    $printRoute = collect(['sales.print', 'sales.invoice', 'sales.invoice.print'])->first(fn ($n) => \Illuminate\Support\Facades\Route::has($n));

    // Whole numbers show no decimals; anything else shows 2.
    $appCurrency = \App\Models\Setting::get('currency', 'AF');
    $af = function ($n, $d = null) use ($appCurrency) {
        $n = (float) $n;
        $d = $d ?? (fmod($n, 1) == 0 ? 0 : 2);
        return number_format($n, $d) . ' ' . $appCurrency;
    };
    $qty = fn ($n) => rtrim(rtrim(number_format((float) $n, 3), '0'), '.');

    $icons = [
        'dollar' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'trend'  => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'file'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/>',
        'wallet' => '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/>',
        'cart'   => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'bars'   => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'box'    => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'alert'  => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'printer'=> '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'clock'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'plus'   => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'user'   => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
        'report' => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
        'eye'    => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'edit'   => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'bell'   => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'lock'   => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    ];
    $ico = fn ($name, $size = 18, $stroke = 'currentColor') =>
        '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$stroke.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name] ?? '').'</svg>';

    $saleStatus = function ($s) {
        if ($s->status === 'cancelled') return 'cancelled';
        $p = strtolower((string) $s->payment_status);
        if (in_array($p, ['paid', 'partial', 'pending'], true)) return $p;
        return ((float) $s->balance) <= 0 ? 'paid' : 'pending';
    };
    $badgeStyle = [
        'paid'      => 'background:var(--success-soft); color:var(--success);',
        'partial'   => 'background:var(--warn-soft); color:var(--warn);',
        'pending'   => 'background:var(--blue-soft); color:var(--blue);',
        'cancelled' => 'background:var(--danger-soft); color:var(--danger);',
    ];
    $alertStyle = [
        'danger' => ['bg' => 'var(--danger-soft)', 'fg' => 'var(--danger)'],
        'warn'   => ['bg' => 'var(--warn-soft)',   'fg' => 'var(--warn)'],
        'info'   => ['bg' => 'var(--blue-soft)',   'fg' => 'var(--blue)'],
    ];

    $palette = ['--accent', '--blue', '--success', '--warn', '--purple', '--danger', '--ink-soft', '--accent-glow'];
    $collected = $totalSales > 0 ? min(100, round(($paidAmount / $totalSales) * 100)) : 0;
    $topRevenue = max(1, (float) ($topProducts->max('revenue') ?? 1));
    $rangeLabel = ['today' => 'today', 'week' => 'this week', 'month' => 'this month', 'year' => 'this year', 'custom' => 'this period'][$range] ?? 'this period';
@endphp

<style>
    /* ---------- Dashboard layout (own grid classes so responsiveness doesn't depend on Tailwind's build) ---------- */
    .dx { max-width: 1280px; margin: 0 auto; padding: 2rem 1.5rem 3rem; background: var(--paper); color: var(--ink); transition: opacity .15s ease; }
    .dx-grid { display: grid; gap: 1rem; margin-bottom: 1rem; }
    .dx-g4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .dx-g6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .dx-g7 { grid-template-columns: repeat(7, minmax(0, 1fr)); }
    .dx-main { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1.25rem; margin-bottom: 1.25rem; }
    .dx-main.even { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    .dx-main.wide-right { grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); }
    .dx-pad { padding: 1.5rem; }
    .dx-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .dx-sub { font-size: .75rem; color: var(--ink-soft); }
    .dx-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* ---------- Tables ---------- */
    .dx-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .dx-table th { text-align: left; font-size: .72rem; font-weight: 600; color: var(--ink-soft); padding: .55rem .75rem; border-bottom: 1px solid var(--line); white-space: nowrap; }
    .dx-table td { padding: .7rem .75rem; border-bottom: 1px solid var(--line); vertical-align: middle; white-space: nowrap; }
    .dx-table tbody tr { transition: background .12s ease; }
    .dx-table tbody tr:hover { background: var(--paper); }
    .dx-table .r { text-align: right; }
    .dx-table tr.row-out { background: var(--danger-soft); }
    .dx-table tr.row-low { background: var(--warn-soft); }
    .dx-table tr.row-out:hover, .dx-table tr.row-low:hover { filter: brightness(.98); }

    /* ---------- Hover lift on the cards people click or scan ---------- */
    .dx .metric-card, .dx .mini-card { transition: transform .15s ease, box-shadow .15s ease; }
    .dx .metric-card:hover, .dx .mini-card:hover { transform: translateY(-2px); }
    .dx .metric-card .value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin-top: .15rem; }

    /* ---------- Quick actions ---------- */
    .dx-qa {
        display: flex; align-items: center; gap: .65rem; padding: .7rem .85rem; min-width: 0;
        background: var(--surface); border: 1px solid var(--line); border-radius: 12px;
        font-size: .82rem; font-weight: 600; color: var(--ink);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 6px 14px -8px rgba(33,38,47,.14);
        transition: transform .12s ease, box-shadow .12s ease;
    }
    a.dx-qa:hover { transform: translateY(-2px); box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 12px 20px -8px rgba(33,38,47,.22); }
    .dx-qa .section-icon { flex: none; width: 32px; height: 32px; }
    .dx-qa span.t { line-height: 1.2; }
    .dx-qa.soon { opacity: .6; cursor: not-allowed; }
    a.dx-qa:focus-visible, .pill:focus-visible, .icon-btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

    /* ---------- Stat tiles inside cards ---------- */
    .dx-stat { background: var(--paper); border-radius: 10px; padding: .85rem 1rem; box-shadow: 0 1px 0 rgba(255,255,255,.7) inset, 0 2px 6px -2px rgba(33,38,47,.10); min-width: 0; }
    .dx-stat .k { font-size: .72rem; color: var(--ink-soft); }
    .dx-stat .v { font-size: 1.05rem; font-weight: 700; margin-top: .15rem; overflow-wrap: anywhere; }
    .dx-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }

    /* ---------- Product thumbnails ---------- */
    .dx-thumb { width: 40px; height: 40px; border-radius: 10px; flex: none; object-fit: cover; background: var(--accent-soft); color: var(--accent-ink); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(33,38,47,.15), 0 1px 0 rgba(255,255,255,.4) inset; }

    /* ---------- Alerts ---------- */
    .dx-alert { display: flex; gap: .75rem; padding: .75rem .85rem; border-radius: 10px; align-items: flex-start; transition: transform .12s ease; }
    a.dx-alert:hover { transform: translateX(2px); }

    /* ---------- Coming-soon panels (ghost layout of what's planned) ---------- */
    .dx-soon-wrap { position: relative; }
    .dx-ghost-area { opacity: .5; pointer-events: none; user-select: none; }
    .ghost { height: 10px; border-radius: 6px; background: linear-gradient(90deg, var(--line), var(--paper), var(--line)); background-size: 200% 100%; animation: dx-shimmer 2.2s linear infinite; }
    .ghost.tall { height: 14px; }
    .dx-soon-msg {
        position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
        width: min(88%, 380px); text-align: center; padding: 1.1rem 1.25rem;
        background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 16px 30px -14px rgba(33,38,47,.35);
    }
    .dx-soon-msg .section-icon { margin: 0 auto .6rem; }
    .dx-printer-ghost { border: 1px dashed var(--line); border-radius: 12px; padding: .9rem; background: var(--paper); }
    .dx-dot { width: 8px; height: 8px; border-radius: 9999px; background: var(--line); flex: none; }
    @keyframes dx-shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
    @media (prefers-reduced-motion: reduce) {
        .ghost { animation: none; }
        .dx .metric-card, .dx .mini-card, .dx-qa, .dx-alert { transition: none; }
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 1100px) {
        .dx-g4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dx-g6 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dx-g7 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .dx-main, .dx-main.even, .dx-main.wide-right { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .dx { padding: 1.25rem 1rem 2.5rem; }
        .dx-g4 { grid-template-columns: minmax(0, 1fr); }
        .dx-g6 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dx-g7 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dx-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dx-pad { padding: 1.1rem; }
        .dx .pill-group { overflow-x: auto; max-width: 100%; }
    }
</style>

<div class="dx">

    {{-- ===================== Header ===================== --}}
    <div class="dx-head" style="align-items:flex-end;">
        <div>
            <h1 class="ledger-font text-3xl mb-1">Dashboard</h1>
            <p class="text-sm" style="color:var(--ink-soft);">
                Pixel Lab &middot; {{ $startDate->format('M j, Y') }} – {{ $endDate->format('M j, Y') }}
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; max-width:100%;">
            {{-- data-dx-nav: clicks are intercepted by the script below, which swaps only the dashboard content --}}
            <div class="pill-group" data-dx-nav role="tablist" aria-label="Reporting period">
                @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
                    <a href="{{ route('dashboard', ['range' => $key]) }}"
                       class="pill {{ $range === $key ? 'active' : '' }}"
                       @if ($range === $key) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
            
        </div>
        
    </div>

    {{-- ===================== Quick actions ===================== --}}
    @php
        $quick = [
            ['New Sale',         'cart',    'sales.create',     'var(--accent-soft)',  'var(--accent-ink)', false],
            ['New Order',        'file',    null,               'var(--blue-soft)',    'var(--blue)',       true],
            ['Add Product',      'box',     'products.create',  'var(--success-soft)', 'var(--success)',    false],
            ['New Printing Job', 'printer', null,               'var(--purple-soft)',  'var(--purple)',     true],
            ['Add Expense',      'wallet',  'expenses.create',  'var(--warn-soft)',    'var(--warn)',       false],
            ['Add Customer',     'user',    'customers.create', 'var(--blue-soft)',    'var(--blue)',       false],
            ['Generate Report',  'report',  'reports.index',    'var(--accent-soft)',  'var(--accent-ink)', false],
        ];
    @endphp
    <div class="dx-grid dx-g7" aria-label="Quick actions">
        @foreach ($quick as [$label, $icon, $route, $bg, $fg, $soon])
            @if ($soon)
                <div class="dx-qa soon" title="Coming soon" aria-disabled="true">
                    <span class="section-icon" style="background:{{ $bg }}; color:{{ $fg }};">{!! $ico($icon, 16) !!}</span>
                    <span class="t">{{ $label }}<br><span style="font-weight:500; font-size:.68rem; color:var(--ink-soft);">Coming soon</span></span>
                </div>
            @else
                <a href="{{ $r($route) }}" class="dx-qa">
                    <span class="section-icon" style="background:{{ $bg }}; color:{{ $fg }};">{!! $ico($icon, 16) !!}</span>
                    <span class="t">{{ $label }}</span>
                </a>
            @endif
        @endforeach
    </div>

    {{-- ===================== KPI cards ===================== --}}
    <div class="dx-grid dx-g4">
        <div class="metric-card">
            <div class="flex justify-between items-start mb-3" style="display:flex; justify-content:space-between;">
                <div class="metric-icon" style="background:var(--accent-soft); color:var(--accent-ink);">{!! $ico('dollar') !!}</div>
            </div>
            <div class="text-xs" style="color:var(--ink-soft);">Total Sales</div>
            <div class="value num">{{ $af($totalSales) }}</div>
            <div class="text-xs mt-1 {{ $salesChange >= 0 ? 'change-up' : 'change-down' }}">
                {{ $salesChange >= 0 ? '↑' : '↓' }} {{ abs($salesChange) }}% vs previous period
            </div>
        </div>

        <div class="metric-card">
            <div class="mb-3" style="display:flex; justify-content:space-between;">
                <div class="metric-icon" style="background:{{ $netProfit >= 0 ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">{!! $ico('trend') !!}</div>
                <span class="badge" style="background:{{ $netProfit >= 0 ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }}; align-self:flex-start;">{{ $profitMargin }}% margin</span>
            </div>
            <div class="text-xs" style="color:var(--ink-soft);">Net Profit</div>
            <div class="value num" style="color:{{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ $af($netProfit) }}</div>
            <div class="text-xs mt-1 {{ $profitChange >= 0 ? 'change-up' : 'change-down' }}">
                {{ $profitChange >= 0 ? '↑' : '↓' }} {{ abs($profitChange) }}% vs previous period
            </div>
        </div>

        <div class="metric-card">
            <div class="mb-3" style="display:flex; justify-content:space-between;">
                <div class="metric-icon" style="background:var(--danger-soft); color:var(--danger);">{!! $ico('file') !!}</div>
            </div>
            <div class="text-xs" style="color:var(--ink-soft);">Total Expenses</div>
            <div class="value num">{{ $af($totalExpenses) }}</div>
            {{-- For expenses, an increase is the bad direction --}}
            <div class="text-xs mt-1 {{ $expensesChange > 0 ? 'change-down' : 'change-up' }}">
                {{ $expensesChange > 0 ? '↑' : '↓' }} {{ abs($expensesChange) }}% vs previous period
            </div>
        </div>

        <div class="metric-card">
            <div class="mb-3" style="display:flex; justify-content:space-between;">
                <div class="metric-icon" style="background:var(--warn-soft); color:var(--warn);">{!! $ico('wallet') !!}</div>
            </div>
            <div class="text-xs" style="color:var(--ink-soft);">Receivables</div>
            <div class="value num">{{ $af($receivables) }}</div>
            <div class="text-xs mt-1" style="color:var(--ink-soft);">
                {{ $receivableCustomers }} {{ $receivableCustomers === 1 ? 'customer owes' : 'customers owe' }} &middot; all-time
            </div>
        </div>
    </div>

    {{-- ===================== Operational cards ===================== --}}
    <div class="dx-grid dx-g7" style="margin-bottom:1.5rem;">
        <div class="mini-card">
            <div><div class="text-xs" style="color:var(--ink-soft);">Total Orders</div><div class="text-lg font-bold num mt-0.5">{{ number_format($totalOrders) }}</div></div>
            {!! $ico('cart', 18, 'var(--ink-soft)') !!}
        </div>
        <div class="mini-card">
            <div><div class="text-xs" style="color:var(--ink-soft);">Avg. Order Value</div><div class="text-lg font-bold num mt-0.5">{{ $af($averageOrderValue) }}</div></div>
            {!! $ico('bars', 18, 'var(--ink-soft)') !!}
        </div>
        <div class="mini-card">
            <div><div class="text-xs" style="color:var(--ink-soft);">Inventory Value</div><div class="text-lg font-bold num mt-0.5">{{ $af($inventoryValue) }}</div></div>
            {!! $ico('box', 18, 'var(--ink-soft)') !!}
        </div>
        <div class="mini-card">
            <div><div class="text-xs" style="color:var(--ink-soft);">Low Stock Items</div>
                <div class="text-lg font-bold num mt-0.5" style="color:{{ ($lowStockCount + $outOfStockCount) > 0 ? 'var(--danger)' : 'var(--ink)' }};">{{ $lowStockCount + $outOfStockCount }}</div></div>
            {!! $ico('alert', 18, ($lowStockCount + $outOfStockCount) > 0 ? 'var(--danger)' : 'var(--ink-soft)') !!}
        </div>
                <div class="mini-card">
            <div><div class="text-xs" style="color:var(--ink-soft);">Purchases</div><div class="text-lg font-bold num mt-0.5">{{ $af($totalPurchases) }}</div></div>
            {!! $ico('layers', 18, 'var(--ink-soft)') !!}
        </div>
        <div class="mini-card" style="opacity:.75;">
            <div><div class="text-xs" style="color:var(--ink-soft);">Active Jobs</div><div class="text-lg font-bold mt-0.5" style="color:var(--ink-soft);">—</div></div>
            <span class="badge" style="background:var(--purple-soft); color:var(--purple);">Soon</span>
        </div>
        <div class="mini-card" style="opacity:.75;">
            <div><div class="text-xs" style="color:var(--ink-soft);">Completed Jobs</div><div class="text-lg font-bold mt-0.5" style="color:var(--ink-soft);">—</div></div>
            <span class="badge" style="background:var(--purple-soft); color:var(--purple);">Soon</span>
        </div>
    </div>

    {{-- ===================== Analytics + Financial summary ===================== --}}
    <div class="dx-grid dx-main">
        <div class="card-3d dx-pad">
            <div class="dx-head" style="margin-bottom:.75rem;">
                <div>
                    <h2 class="ledger-font text-lg mb-1">Sales &amp; Profit Analytics</h2>
                    <p class="dx-sub">Sales, expenses and profit &middot; {{ $trendNote }}</p>
                </div>
                <div class="pill-group" id="chartType" role="group" aria-label="Chart type">
                    <button type="button" class="pill active" data-type="line">Line</button>
                    <button type="button" class="pill" data-type="bar">Bars</button>
                </div>
            </div>
            <div style="height:300px; position:relative;">
                <canvas id="trendChart" role="img" aria-label="Sales, expenses and profit over time"></canvas>
            </div>
        </div>

        <div class="card-3d dx-pad">
            <h2 class="ledger-font text-lg mb-1">Financial Summary</h2>
            <p class="dx-sub mb-2">{{ ucfirst($rangeLabel) }}</p>

            <div class="dx-sub" style="font-weight:600; margin:.5rem 0 .1rem; color:var(--success);">Income</div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Revenue</span>
                <span class="num text-sm font-medium">{{ $af($totalSales) }}</span>
            </div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Amount received</span>
                <span class="num text-sm font-medium" style="color:var(--success);">{{ $af($paidAmount) }}</span>
            </div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Outstanding receivables</span>
                <span class="num text-sm font-medium" style="color:var(--warn);">{{ $af($periodOutstanding) }}</span>
            </div>

            <div class="dx-sub" style="font-weight:600; margin:.9rem 0 .1rem; color:var(--danger);">Expenses</div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Cost of goods sold</span>
                <span class="num text-sm font-medium">{{ $af($productCost) }}</span>
            </div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Operating expenses</span>
                <span class="num text-sm font-medium">{{ $af($totalExpenses) }}</span>
            </div>

            <div class="row-raised mt-3" style="margin-top:.9rem;">
                <div class="flex justify-between" style="margin-bottom:.5rem;">
                    <span class="text-sm font-semibold">Gross profit</span>
                    <span class="num text-sm font-semibold" style="color:{{ $grossProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ $af($grossProfit) }}</span>
                </div>
                <div class="flex justify-between" style="margin-bottom:.5rem;">
                    <span class="font-bold">Net profit</span>
                    <span class="num font-bold text-lg value-strong" style="color:{{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ $af($netProfit) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm" style="color:var(--ink-soft);">Profit margin</span>
                    <span class="num text-sm font-semibold">{{ $profitMargin }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Sales overview + Alerts ===================== --}}
    <div class="dx-grid dx-main">
        <div class="card-3d dx-pad">
            <div class="dx-head" style="margin-bottom:1rem;">
                <div>
                    <h2 class="ledger-font text-lg mb-1">Sales Overview</h2>
                    <p class="dx-sub">How {{ $rangeLabel }} is performing</p>
                </div>
                <span class="badge" style="background:var(--success-soft); color:var(--success);">{{ $collected }}% collected</span>
            </div>

            <div class="dx-stats">
                <div class="dx-stat"><div class="k">Total sales</div><div class="v num">{{ $af($totalSales) }}</div></div>
                <div class="dx-stat"><div class="k">Paid</div><div class="v num" style="color:var(--success);">{{ $af($paidAmount) }}</div></div>
                <div class="dx-stat"><div class="k">Outstanding</div><div class="v num" style="color:var(--warn);">{{ $af($periodOutstanding) }}</div></div>
                <div class="dx-stat"><div class="k">Orders</div><div class="v num">{{ number_format($totalOrders) }}</div></div>
                <div class="dx-stat"><div class="k">Avg. order value</div><div class="v num">{{ $af($averageOrderValue) }}</div></div>
                <div class="dx-stat">
                    <div class="k">Best-selling product</div>
                    <div class="v" style="font-size:.9rem;">{{ $bestProduct->name ?? '—' }}</div>
                    @if ($bestProduct)<div class="k">{{ $qty($bestProduct->units) }} units</div>@endif
                </div>
                <div class="dx-stat" style="grid-column: span 3;">
                    <div class="k">Best customer</div>
                    <div class="v" style="font-size:.9rem;">
                        {{ $bestCustomer->customer->name ?? '—' }}
                        @if ($bestCustomer)<span class="num" style="font-weight:500; color:var(--ink-soft);"> &middot; {{ $af($bestCustomer->spent) }}</span>@endif
                    </div>
                </div>
            </div>

            <div style="margin-top:1.1rem;">
                <div class="dx-sub" style="margin-bottom:.35rem;">Sales trend</div>
                <div style="height:90px;"><canvas id="sparkChart" role="img" aria-label="Sales trend"></canvas></div>
            </div>
        </div>

        <div class="card-3d dx-pad">
            <div class="dx-head" style="margin-bottom:.9rem;">
                <div>
                    <h2 class="ledger-font text-lg mb-1">Alerts</h2>
                    <p class="dx-sub">Things that need attention</p>
                </div>
                @if (count($alerts))
                    <span class="badge" style="background:var(--danger-soft); color:var(--danger);">{{ count($alerts) }}</span>
                @endif
            </div>

            <div style="display:flex; flex-direction:column; gap:.6rem;">
                @forelse ($alerts as $a)
                    @php $s = $alertStyle[$a['level']] ?? $alertStyle['info']; @endphp
                    <a href="{{ $r($a['route']) }}" class="dx-alert" style="background:{{ $s['bg'] }};">
                        <span style="color:{{ $s['fg'] }}; flex:none; margin-top:1px;">{!! $ico($a['icon'], 18) !!}</span>
                        <span style="min-width:0;">
                            <span class="text-sm font-semibold" style="display:block; color:var(--ink);">{{ $a['title'] }}</span>
                            <span class="text-xs num" style="color:var(--ink-soft); display:block; overflow-wrap:anywhere;">
                                @if (! empty($a['amount'])) {{ $af($a['amount']) }} outstanding @else {{ $a['detail'] }} @endif
                            </span>
                        </span>
                    </a>
                @empty
                    <div style="text-align:center; padding:1.25rem .5rem; color:var(--ink-soft);">
                        <div style="color:var(--success); margin-bottom:.4rem;">{!! $ico('check', 26) !!}</div>
                        <div class="text-sm font-semibold" style="color:var(--ink);">All clear</div>
                        <div class="text-xs">Stock levels and payments look healthy.</div>
                    </div>
                @endforelse

                {{-- TODO(jobs/printers/orders): failed jobs, printer maintenance and pending-order alerts go here --}}
                <div class="dx-alert" style="border:1px dashed var(--line);">
                    <span style="color:var(--purple); flex:none;">{!! $ico('printer', 18) !!}</span>
                    <span class="text-xs" style="color:var(--ink-soft);">Failed jobs, printer maintenance and pending orders will show here once those modules are added.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Recent sales ===================== --}}
    <div class="card-3d dx-pad" style="margin-bottom:1.25rem;">
        <div class="dx-head" style="margin-bottom:.75rem;">
            <div>
                <h2 class="ledger-font text-lg mb-1">Recent Sales</h2>
                <p class="dx-sub">Latest 8 invoices</p>
            </div>
            <a href="{{ $r('sales.index') }}" class="text-sm font-semibold" style="color:var(--accent-ink);">View all sales</a>
        </div>
        <div class="dx-scroll">
            <table class="dx-table" style="min-width:860px;">
                <thead>
                    <tr>
                        <th>Invoice #</th><th>Customer</th><th>Date</th><th class="r">Items</th>
                        <th class="r">Total</th><th class="r">Paid</th><th class="r">Balance</th>
                        <th>Status</th><th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recentSales as $sale)
                    @php $st = $saleStatus($sale); @endphp
                    <tr>
                        <td class="font-semibold">{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td style="color:var(--ink-soft);">{{ $sale->sold_at?->format('M j, Y') }}</td>
                        <td class="r num">{{ $sale->items_count }}</td>
                        <td class="r num font-semibold">{{ $af($sale->total) }}</td>
                        <td class="r num" style="color:var(--success);">{{ $af($sale->amount_paid) }}</td>
                        <td class="r num" style="color:{{ $sale->balance > 0 ? 'var(--danger)' : 'var(--ink-soft)' }};">{{ $af($sale->balance) }}</td>
                        <td><span class="badge" style="{{ $badgeStyle[$st] }}">{{ ucfirst($st) }}</span></td>
                        <td class="r">
                            <span style="display:inline-flex; gap:2px;">
                                <a href="{{ $r('sales.show', $sale) }}" class="icon-btn" title="View" aria-label="View {{ $sale->invoice_number }}" style="color:var(--ink-soft);">{!! $ico('eye', 16) !!}</a>
                                <a href="{{ $r('sales.edit', $sale) }}" class="icon-btn" title="Edit" aria-label="Edit {{ $sale->invoice_number }}" style="color:var(--ink-soft);">{!! $ico('edit', 16) !!}</a>
                                <a href="{{ $printRoute ? route($printRoute, $sale) : '#' }}" class="icon-btn" title="Print invoice" aria-label="Print invoice {{ $sale->invoice_number }}" style="color:var(--ink-soft);" @if ($printRoute) target="_blank" @endif>{!! $ico('printer', 16) !!}</a>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--ink-soft);">No sales yet. <a href="{{ $r('sales.create') }}" style="color:var(--accent-ink); font-weight:600;">Create your first sale</a>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== Printing jobs + Printers (coming soon) ===================== --}}
    <div class="dx-grid dx-main">
        <div class="card-3d dx-pad">
            <div class="dx-head" style="margin-bottom:.75rem;">
                <div>
                    <h2 class="ledger-font text-lg mb-1">Printing Jobs</h2>
                    <p class="dx-sub">Queue, progress and status of every print</p>
                </div>
                <span class="badge" style="background:var(--purple-soft); color:var(--purple);">Coming soon</span>
            </div>

            {{-- TODO(jobs): replace this block with a real @foreach over active PrintingJob records --}}
            <div class="dx-soon-wrap">
                <div class="dx-ghost-area dx-scroll" aria-hidden="true">
                    <table class="dx-table" style="min-width:720px;">
                        <thead><tr><th>Job ID</th><th>Customer</th><th>Model</th><th>Printer</th><th>Material</th><th>Est. time</th><th style="width:130px;">Progress</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach ([62, 38, 84, 0] as $w)
                                <tr>
                                    <td><div class="ghost" style="width:52px;"></div></td>
                                    <td><div class="ghost" style="width:80px;"></div></td>
                                    <td><div class="ghost" style="width:96px;"></div></td>
                                    <td><div class="ghost" style="width:64px;"></div></td>
                                    <td><div class="ghost" style="width:40px;"></div></td>
                                    <td><div class="ghost" style="width:52px;"></div></td>
                                    <td><div class="bar-track"><div class="bar-fill" style="width:{{ $w }}%; opacity:.5;"></div></div></td>
                                    <td><div class="ghost tall" style="width:64px; border-radius:9999px;"></div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dx-soon-msg">
                    <div class="section-icon" style="background:var(--purple-soft); color:var(--purple);">{!! $ico('layers', 18) !!}</div>
                    <div class="text-sm font-semibold">Printing queue is on its way</div>
                    <p class="text-xs" style="color:var(--ink-soft); margin-top:.25rem;">Queued, printing, paused and failed jobs with live progress will appear here.</p>
                </div>
            </div>
        </div>

        <div class="card-3d dx-pad">
            <div class="dx-head" style="margin-bottom:.75rem;">
                <div>
                    <h2 class="ledger-font text-lg mb-1">Printer Status</h2>
                    <p class="dx-sub">Online, printing, maintenance, error</p>
                </div>
                <span class="badge" style="background:var(--purple-soft); color:var(--purple);">Coming soon</span>
            </div>

            {{-- TODO(printers): replace with a @foreach over Printer records (name, model, status, current job, material, progress, time left) --}}
            <div class="dx-soon-wrap">
                <div class="dx-ghost-area" style="display:grid; gap:.75rem;" aria-hidden="true">
                    @foreach ([67, 0] as $w)
                        <div class="dx-printer-ghost">
                            <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.7rem;">
                                <span class="dx-dot"></span>
                                <div class="ghost tall" style="width:90px;"></div>
                                <div class="ghost" style="width:50px; margin-left:auto;"></div>
                            </div>
                            <div class="ghost" style="width:70%; margin-bottom:.6rem;"></div>
                            <div class="bar-track"><div class="bar-fill" style="width:{{ $w }}%; opacity:.5;"></div></div>
                            <div class="ghost" style="width:45%; margin-top:.6rem;"></div>
                        </div>
                    @endforeach
                </div>
                <div class="dx-soon-msg" style="width:min(86%, 300px);">
                    <div class="section-icon" style="background:var(--purple-soft); color:var(--purple);">{!! $ico('printer', 18) !!}</div>
                    <div class="text-sm font-semibold">Printer monitoring</div>
                    <p class="text-xs" style="color:var(--ink-soft); margin-top:.25rem;">Add your printers to see status, current job and time remaining.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Inventory ===================== --}}
    <div class="card-3d dx-pad" style="margin-bottom:1.25rem;">
        <div class="dx-head" style="margin-bottom:1rem;">
            <div>
                <h2 class="ledger-font text-lg mb-1">Inventory Overview</h2>
                <p class="dx-sub">Products needing attention are listed first</p>
            </div>
            <a href="{{ $r('products.index') }}" class="text-sm font-semibold" style="color:var(--accent-ink);">View all products</a>
        </div>

        <div class="dx-grid dx-g4" style="margin-bottom:1rem;">
            <div class="dx-stat"><div class="k">Total products</div><div class="v num">{{ number_format($totalProducts) }}</div></div>
            <div class="dx-stat"><div class="k">Inventory value</div><div class="v num">{{ $af($inventoryValue) }}</div></div>
            <div class="dx-stat" style="{{ $lowStockCount ? 'background:var(--warn-soft);' : '' }}"><div class="k">Low stock</div><div class="v num" style="color:{{ $lowStockCount ? 'var(--warn)' : 'var(--ink)' }};">{{ $lowStockCount }}</div></div>
            <div class="dx-stat" style="{{ $outOfStockCount ? 'background:var(--danger-soft);' : '' }}"><div class="k">Out of stock</div><div class="v num" style="color:{{ $outOfStockCount ? 'var(--danger)' : 'var(--ink)' }};">{{ $outOfStockCount }}</div></div>
        </div>

        <div class="dx-scroll">
            <table class="dx-table" style="min-width:820px;">
                <thead>
                    <tr>
                        <th>Product</th><th>SKU</th><th>Category</th>
                        <th class="r">Stock</th><th class="r">Min.</th><th class="r">Cost</th><th class="r">Price</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($inventoryProducts as $p)
                    @php
                        $state = $p->current_stock <= 0 ? 'out' : ($p->isLowStock() ? 'low' : 'ok');
                        $stateBadge = [
                            'out' => ['Out of stock', 'background:var(--danger-soft); color:var(--danger);'],
                            'low' => ['Low stock',    'background:var(--warn-soft); color:var(--warn);'],
                            'ok'  => ['In stock',     'background:var(--success-soft); color:var(--success);'],
                        ][$state];
                    @endphp
                    <tr class="{{ $state === 'out' ? 'row-out' : ($state === 'low' ? 'row-low' : '') }}">
                        <td>
                            <div style="display:flex; align-items:center; gap:.65rem;">
                                @if ($p->image_path)
<img class="dx-thumb" src="{{ route('media', ['path' => $p->image_path]) }}" alt="" loading="lazy">
                                @else
                                    <span class="dx-thumb">{!! $ico('box', 18) !!}</span>
                                @endif
                                <span class="font-semibold">{{ $p->name }}</span>
                            </div>
                        </td>
                        <td class="num" style="color:var(--ink-soft);">{{ $p->sku ?: '—' }}</td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td class="r num font-semibold" style="color:{{ $state === 'ok' ? 'var(--ink)' : ($state === 'out' ? 'var(--danger)' : 'var(--warn)') }};">{{ $qty($p->current_stock) }} {{ $p->unit }}</td>
                        <td class="r num" style="color:var(--ink-soft);">{{ $qty($p->min_stock_level) }}</td>
                        <td class="r num">{{ $af($p->cost_price) }}</td>
                        <td class="r num">{{ $af($p->selling_price) }}</td>
                        <td><span class="badge" style="{{ $stateBadge[1] }}">{{ $stateBadge[0] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--ink-soft);">No products yet. <a href="{{ $r('products.create') }}" style="color:var(--accent-ink); font-weight:600;">Add a product</a>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== Expenses + Top products ===================== --}}
    <div class="dx-grid dx-main wide-right">
        <div class="card-3d dx-pad">
            <h2 class="ledger-font text-lg mb-1">Expense Overview</h2>
            <p class="dx-sub" style="margin-bottom:1rem;">Where the money went {{ $rangeLabel }}</p>

            @if ($expenseBreakdown->isEmpty())
                <div style="text-align:center; padding:2rem 0; color:var(--ink-soft);">
                    <div class="text-sm font-semibold" style="color:var(--ink);">No expenses recorded</div>
                    <a href="{{ $r('expenses.create') }}" class="text-xs font-semibold" style="color:var(--accent-ink);">Add an expense</a>
                </div>
            @else
                <div style="position:relative; height:200px;">
                    <canvas id="expenseChart" role="img" aria-label="Expense distribution by category"></canvas>
                    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                        <span class="dx-sub">Total</span>
                        <span class="num font-bold" style="font-size:1rem;">{{ $af($totalExpenses) }}</span>
                    </div>
                </div>
                <div style="margin-top:1rem; display:grid; gap:.45rem;">
                    @foreach ($expenseBreakdown as $i => $row)
                        <div style="display:flex; align-items:center; gap:.55rem; font-size:.82rem;">
                            <span style="width:10px; height:10px; border-radius:3px; background:var({{ $palette[$i % count($palette)] }}); flex:none;"></span>
                            <span style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $row->name }}</span>
                            <span class="num" style="color:var(--ink-soft);">{{ $row->pct }}%</span>
                            <span class="num font-semibold" style="min-width:76px; text-align:right;">{{ $af($row->total) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card-3d dx-pad">
            <h2 class="ledger-font text-lg mb-1">Top Products</h2>
            <p class="dx-sub" style="margin-bottom:.75rem;">Ranked by revenue {{ $rangeLabel }}</p>
            <div class="dx-scroll">
                <table class="dx-table" style="min-width:560px;">
                    <thead><tr><th>Product</th><th class="r">Units</th><th class="r">Revenue</th><th class="r">Profit</th><th class="r">Margin</th></tr></thead>
                    <tbody>
                    @forelse ($topProducts as $tp)
                        <tr>
                            <td style="min-width:200px;">
                                <div style="display:flex; align-items:center; gap:.65rem;">
                                    @if ($tp->image_path)
<img class="dx-thumb" src="{{ route('media', ['path' => $tp->image_path]) }}" alt="" loading="lazy">
                                    @else
                                        <span class="dx-thumb">{!! $ico('box', 18) !!}</span>
                                    @endif
                                    <div style="flex:1; min-width:0;">
                                        <div class="font-semibold">{{ $tp->name }}</div>
                                        <div class="bar-track" style="margin-top:.35rem; height:5px;"><div class="bar-fill" style="width:{{ round(($tp->revenue / $topRevenue) * 100) }}%;"></div></div>
                                    </div>
                                </div>
                            </td>
                            <td class="r num">{{ $qty($tp->units_sold) }}</td>
                            <td class="r num font-semibold">{{ $af($tp->revenue) }}</td>
                            <td class="r num" style="color:{{ $tp->profit >= 0 ? 'var(--success)' : 'var(--danger)' }};">{{ $af($tp->profit) }}</td>
                            <td class="r"><span class="badge" style="{{ $tp->margin >= 30 ? $badgeStyle['paid'] : ($tp->margin >= 10 ? $badgeStyle['partial'] : $badgeStyle['cancelled']) }}">{{ $tp->margin }}%</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--ink-soft);">No product sales {{ $rangeLabel }} yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

        {{-- ===================== Purchases ===================== --}}
    <div class="dx-grid dx-main">
        <div class="card-3d dx-pad">
            <div class="dx-head" style="margin-bottom:.75rem;">
                <div>
                    <h2 class="ledger-font text-lg mb-1">Recent Purchases</h2>
                    <p class="dx-sub">Stock and supply orders {{ $rangeLabel }}</p>
                </div>
               
            </div>
            <div class="dx-scroll">
                <table class="dx-table" style="min-width:640px;">
                    <thead>
                        <tr><th>#</th><th>Item</th><th>Supplier</th><th>Date</th><th class="r">Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    @forelse ($recentPurchases as $purchase)
                        <tr>
                            <td class="font-semibold">#{{ $purchase->id }}</td>
                            <td>{{ $purchase->displayName() }}</td>
                            <td style="color:var(--ink-soft);">{{ $purchase->supplier->name ?? 'No supplier' }}</td>
                            <td style="color:var(--ink-soft);">{{ ($purchase->order_date ?? $purchase->created_at)?->format('M j, Y') }}</td>
                            <td class="r num font-semibold">{{ $af($purchase->total_cost_base) }}</td>
                            <td>
                                @if ((float) $purchase->amount_due_base > 0.005)
                                    <span class="badge" style="{{ $badgeStyle['partial'] }}">Due</span>
                                @else
                                    <span class="badge" style="{{ $badgeStyle['paid'] }}">Paid</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:2rem; color:var(--ink-soft);">No purchases {{ $rangeLabel }}.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-3d dx-pad">
            <h2 class="ledger-font text-lg mb-1">Purchases Summary</h2>
            <p class="dx-sub mb-2">{{ ucfirst($rangeLabel) }}</p>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Orders</span>
                <span class="num text-sm font-medium">{{ $purchasesCount }}</span>
            </div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Total purchased</span>
                <span class="num text-sm font-medium">{{ $af($totalPurchases) }}</span>
            </div>
            <div class="row-flat flex justify-between" style="border-bottom:1px solid var(--line);">
                <span class="text-sm" style="color:var(--ink-soft);">Paid</span>
                <span class="num text-sm font-medium" style="color:var(--success);">{{ $af($purchasesPaid) }}</span>
            </div>
            <div class="row-raised" style="margin-top:.9rem;">
                <div class="flex justify-between">
                    <span class="font-bold">Owed to suppliers</span>
                    <span class="num font-bold" style="color:{{ $purchasesDue > 0 ? 'var(--danger)' : 'var(--ink)' }};">{{ $af($purchasesDue) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart data for the current period. Lives inside .dx so it is swapped together with the content. --}}
    <script type="application/json" id="dx-data">{!! json_encode([
        'trend'     => $trend,
        'expenses'  => $expenseBreakdown->pluck('total')->values(),
        'expLabels' => $expenseBreakdown->pluck('name')->values(),
        'palette'   => $palette,
    ], JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
</div>

{{-- ===================== Charts + in-page period switching ===================== --}}
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
(function () {
    // If this script is evaluated again, don't bind listeners twice; just redraw.
    if (window.__dxBound) { window.__dxRedraw && window.__dxRedraw(); return; }
    window.__dxBound = true;

    let data = JSON.parse(document.getElementById('dx-data').textContent);
    let type = 'line', trendChart, sparkChart, donut;

    const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
    const fmt = v => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(v) + ' ' + @json($appCurrency);
    const compact = v => new Intl.NumberFormat('en-US', { notation: 'compact' }).format(v);
    const rgba = (hex, a) => {
        const h = hex.replace('#', '');
        const n = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
        return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${a})`;
    };

    function drawTrend() {
        const el = document.getElementById('trendChart');
        if (!el) return;
        const trend = data.trend;
        const c = { sales: css('--accent'), exp: css('--danger'), profit: css('--success'), ink: css('--ink-soft'), line: css('--line'), surface: css('--surface'), text: css('--ink') };
        const bar = type === 'bar';
        const ds = (label, d, color, fill) => ({
            label, data: d, borderColor: color, borderWidth: 2, tension: .35,
            pointRadius: trend.labels.length > 40 ? 0 : 3, pointHoverRadius: 5, pointBackgroundColor: color,
            backgroundColor: bar ? color : (fill ? (ctx) => {
                const a = ctx.chart.chartArea; if (!a) return null;
                const g = ctx.chart.ctx.createLinearGradient(0, a.top, 0, a.bottom);
                g.addColorStop(0, rgba(color, .22)); g.addColorStop(1, rgba(color, 0)); return g;
            } : color),
            fill: !bar && !!fill, borderRadius: bar ? 5 : 0, maxBarThickness: 22,
        });
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(el, {
            type,
            data: { labels: trend.labels, datasets: [
                ds('Sales', trend.sales, c.sales, true),
                ds('Expenses', trend.expenses, c.exp, false),
                ds('Profit', trend.profit, c.profit, false),
            ] },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                animation: { duration: 700, easing: 'easeOutQuart' },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, color: c.ink } },
                    tooltip: {
                        backgroundColor: c.text, titleColor: c.surface, bodyColor: c.surface, padding: 10, cornerRadius: 8,
                        callbacks: { label: ctx => ` ${ctx.dataset.label}: ${fmt(ctx.parsed.y)}` },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: c.ink, maxRotation: 0, autoSkip: true } },
                    y: { grid: { color: c.line }, ticks: { color: c.ink, callback: compact }, grace: '5%' },
                },
            },
        });
    }

    function drawSpark() {
        const el = document.getElementById('sparkChart');
        if (!el) return;
        const color = css('--accent');
        if (sparkChart) sparkChart.destroy();
        sparkChart = new Chart(el, {
            type: 'line',
            data: { labels: data.trend.labels, datasets: [{
                data: data.trend.sales, borderColor: color, borderWidth: 2, tension: .35, pointRadius: 0, pointHoverRadius: 4, fill: true,
                backgroundColor: ctx => {
                    const a = ctx.chart.chartArea; if (!a) return null;
                    const g = ctx.chart.ctx.createLinearGradient(0, a.top, 0, a.bottom);
                    g.addColorStop(0, rgba(color, .25)); g.addColorStop(1, rgba(color, 0)); return g;
                },
            }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + fmt(ctx.parsed.y) } } },
                scales: { x: { display: false }, y: { display: false, beginAtZero: true } },
            },
        });
    }

    function drawDonut() {
        const el = document.getElementById('expenseChart');
        if (!el) return;
        if (donut) donut.destroy();
        donut = new Chart(el, {
            type: 'doughnut',
            data: { labels: data.expLabels, datasets: [{
                data: data.expenses,
                backgroundColor: data.expenses.map((_, i) => css(data.palette[i % data.palette.length])),
                borderColor: css('--surface'), borderWidth: 3, hoverOffset: 6,
            }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                animation: { animateRotate: true, duration: 800 },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${fmt(ctx.parsed)}` } } },
            },
        });
    }

    function drawAll() { drawTrend(); drawSpark(); drawDonut(); }

    // Called after the dashboard content is swapped: re-read the new data and redraw.
    window.__dxRedraw = () => {
        const d = document.getElementById('dx-data');
        if (d) data = JSON.parse(d.textContent);
        type = 'line';
        drawAll();
    };

    /* ---- Line / Bars toggle (delegated, so it survives content swaps) ---- */
    document.addEventListener('click', e => {
        const btn = e.target.closest('#chartType .pill');
        if (!btn) return;
        type = btn.dataset.type;
        document.querySelectorAll('#chartType .pill').forEach(b => b.classList.toggle('active', b === btn));
        drawTrend();
    });

    /* ---- Period pills: swap only the dashboard content, not the whole app ---- */
    async function go(url, push) {
        const root = document.querySelector('.dx');
        if (!root) { location.href = url; return; }
        root.style.opacity = '.55';
        root.style.pointerEvents = 'none';
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error(res.status);
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const next = doc.querySelector('.dx');
            if (!next || !doc.getElementById('dx-data')) throw new Error('no dashboard');
            [trendChart, sparkChart, donut].forEach(c => c && c.destroy());
            trendChart = sparkChart = donut = null;
            root.replaceWith(next);
            if (push) history.pushState({ dx: 1 }, '', url);
            window.__dxRedraw();
        } catch (err) {
            location.href = url;   // fall back to a normal navigation
        }
    }

    document.addEventListener('click', e => {
        const a = e.target.closest('[data-dx-nav] a');
        if (!a || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        e.preventDefault();
        if (a.href === location.href) return;
        go(a.href, true);
    });
    window.addEventListener('popstate', () => {
        if (document.querySelector('[data-dx-nav]')) go(location.href, false);
    });

    /* Redraw with the right colours when the app switches light/dark theme. */
    new MutationObserver(drawAll).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    drawAll();
})();
</script>
</x-layouts.pixel>