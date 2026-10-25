<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->get('range', 'month');

        // A custom range without dates falls back to the current month.
        if ($range === 'custom' && (! $request->filled('start_date') || ! $request->filled('end_date'))) {
            $range = 'month';
        }

        [$startDate, $endDate, $previousStart, $previousEnd] = $this->getDateRange($range, $request);

        // Date filtering uses `sold_at` (the business date), never `created_at`,
        // so numbers agree with the rest of the app's reports.
        $periodSales = fn () => Sale::whereBetween('sold_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');

        /* ------------------------------------------------------------------
         | Core financials
         * ----------------------------------------------------------------*/
        $totalSales        = (float) $periodSales()->sum('total');
        $totalOrders       = (int) $periodSales()->count();
        $paidAmount        = (float) $periodSales()->sum('amount_paid');
        $periodOutstanding = (float) $periodSales()->sum('balance');

        $totalExpenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $productCost   = $this->productCost($startDate, $endDate);


        /* ------------------------------------------------------------------
 | Purchases (stock & supply orders in the period, base currency)
 * ----------------------------------------------------------------*/
$periodPurchases = Purchase::with('supplier')
    ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'cancelled'))
    ->whereBetween(DB::raw('date(COALESCE(order_date, created_at))'), [$startDate->toDateString(), $endDate->toDateString()])
    ->get();

$totalPurchases  = (float) $periodPurchases->sum(fn ($p) => (float) $p->total_cost_base);
$purchasesDue    = (float) $periodPurchases->sum(fn ($p) => (float) $p->amount_due_base);
$purchasesPaid   = $totalPurchases - $purchasesDue;
$purchasesCount  = $periodPurchases->count();
$recentPurchases = $periodPurchases->sortByDesc('order_date')->take(6)->values();

        $grossProfit       = $totalSales - $productCost;
        $netProfit         = $grossProfit - $totalExpenses;
        $profitMargin      = $totalSales > 0 ? round(($netProfit / $totalSales) * 100, 1) : 0;
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        /* ------------------------------------------------------------------
         | Previous period (for % change badges)
         * ----------------------------------------------------------------*/
        $previousSales = (float) Sale::whereBetween('sold_at', [$previousStart, $previousEnd])
            ->where('status', '!=', 'cancelled')->sum('total');
        $previousExpenses = (float) Expense::whereBetween('expense_date', [$previousStart, $previousEnd])->sum('amount');
        $previousProfit   = $previousSales - $this->productCost($previousStart, $previousEnd) - $previousExpenses;

        $salesChange    = $this->percentageChange($previousSales, $totalSales);
        $profitChange   = $this->percentageChange($previousProfit, $netProfit);
        $expensesChange = $this->percentageChange($previousExpenses, $totalExpenses);

        /* ------------------------------------------------------------------
         | Receivables — all-time outstanding balances (not period-bound)
         * ----------------------------------------------------------------*/
        $unpaid = fn () => Sale::where('status', '!=', 'cancelled')->where('balance', '>', 0);

        $receivables         = (float) $unpaid()->sum('balance');
        $receivableCustomers = (int) $unpaid()->whereNotNull('customer_id')->distinct()->count('customer_id');
        $unpaidInvoiceCount  = (int) $unpaid()->count();
        $overdueInvoiceCount = (int) $unpaid()->where('sold_at', '<', Carbon::now()->subDays(30))->count();

        /* ------------------------------------------------------------------
         | Inventory
         * ----------------------------------------------------------------*/
        $totalProducts  = Product::count();
        $inventoryValue = (float) Product::selectRaw('COALESCE(SUM(current_stock * cost_price), 0) as value')->value('value');
        $outOfStockCount = Product::where('current_stock', '<=', 0)->count();
        $lowStockCount   = Product::where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'min_stock_level')->count();

        // Items needing attention first (out of stock, then low), then the rest.
        $inventoryProducts = Product::with('category')
            ->orderByRaw('CASE WHEN current_stock <= 0 THEN 0 WHEN current_stock <= min_stock_level THEN 1 ELSE 2 END')
            ->orderBy('name')
            ->limit(8)
            ->get();

        /* ------------------------------------------------------------------
         | Sales lists & rankings
         * ----------------------------------------------------------------*/
        $recentSales = Sale::with('customer')->withCount('items')
            ->latest('sold_at')->limit(8)->get();

        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sold_at', [$startDate, $endDate])
            ->where('sales.status', '!=', 'cancelled')
            ->select(
                'products.id',
                'products.name',
                'products.image_path',
                DB::raw('SUM(sale_items.quantity) as units_sold'),
                DB::raw('SUM(sale_items.line_total) as revenue'),
                DB::raw('SUM(sale_items.line_total - ((sale_items.quantity * sale_items.unit_cost) + sale_items.extra_cost)) as profit')
            )
            ->groupBy('products.id', 'products.name', 'products.image_path')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get()
            ->map(function ($p) {
                $p->margin = $p->revenue > 0 ? round(($p->profit / $p->revenue) * 100, 1) : 0;
                return $p;
            });

        $bestProduct = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sold_at', [$startDate, $endDate])
            ->where('sales.status', '!=', 'cancelled')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as units'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units')
            ->first();

        $bestCustomer = $periodSales()
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('SUM(total) as spent'))
            ->groupBy('customer_id')
            ->orderByDesc('spent')
            ->with('customer')
            ->first();

        /* ------------------------------------------------------------------
         | Expense breakdown (donut)
         * ----------------------------------------------------------------*/
       $expenseBreakdown = Expense::leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
    ->whereBetween('expenses.expense_date', [$startDate, $endDate])
    ->selectRaw("COALESCE(expense_categories.name, 'Uncategorized') as name, SUM(expenses.amount) as total")
    ->groupBy(DB::raw("COALESCE(expense_categories.name, 'Uncategorized')"))
    ->orderByDesc('total')
    ->get();

        if ($expenseBreakdown->count() > 7) {
            $rest = $expenseBreakdown->slice(7)->sum('total');
            $expenseBreakdown = $expenseBreakdown->take(7)->push((object) ['name' => 'Other', 'total' => $rest]);
        }
        $expenseBreakdown = $expenseBreakdown->map(function ($row) use ($totalExpenses) {
            $row->total = (float) $row->total;
            $row->pct   = $totalExpenses > 0 ? round(($row->total / $totalExpenses) * 100, 1) : 0;
            return $row;
        })->values();

        /* ------------------------------------------------------------------
         | Chart + alerts
         * ----------------------------------------------------------------*/
        $trend     = $this->getTrend($startDate, $endDate, $range);
        $trendNote = $range === 'today' ? 'Last 7 days, ending today' : 'Selected period';
        $alerts    = $this->buildAlerts(
            $outOfStockCount, $lowStockCount, $unpaidInvoiceCount, $receivables, $overdueInvoiceCount
        );

               return view('dashboard.index', compact(
            'range', 'startDate', 'endDate',
            'totalSales', 'totalOrders', 'paidAmount', 'periodOutstanding',
            'totalExpenses', 'productCost', 'grossProfit', 'netProfit', 'profitMargin', 'averageOrderValue',
            'salesChange', 'profitChange', 'expensesChange',
            'receivables', 'receivableCustomers', 'unpaidInvoiceCount',
            'totalProducts', 'inventoryValue', 'lowStockCount', 'outOfStockCount', 'inventoryProducts',
            'recentSales', 'topProducts', 'bestProduct', 'bestCustomer',
            'expenseBreakdown', 'trend', 'trendNote', 'alerts',
            'totalPurchases', 'purchasesPaid', 'purchasesDue', 'purchasesCount', 'recentPurchases'
        ));
    }

    /**
     * Cost of goods sold: quantity * unit_cost snapshot + extra_cost per sale line,
     * i.e. the cost recorded at the moment of sale, not today's cost.
     */
    private function productCost($start, $end): float
    {
        return (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sold_at', [$start, $end])
            ->where('sales.status', '!=', 'cancelled')
            ->sum(DB::raw('(sale_items.quantity * sale_items.unit_cost) + sale_items.extra_cost'));
    }

    private function getDateRange($range, Request $request)
    {
        switch ($range) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::today()->endOfDay();
                $previousStart = Carbon::yesterday();
                $previousEnd = Carbon::yesterday()->endOfDay();
                break;

            case 'week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $previousStart = Carbon::now()->subWeek()->startOfWeek();
                $previousEnd = Carbon::now()->subWeek()->endOfWeek();
                break;

            case 'year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                $previousStart = Carbon::now()->subYear()->startOfYear();
                $previousEnd = Carbon::now()->subYear()->endOfYear();
                break;

            case 'custom':
                $start = Carbon::parse($request->get('start_date'))->startOfDay();
                $end = Carbon::parse($request->get('end_date'))->endOfDay();
                $days = (int) $start->diffInDays($end) + 1;
                $previousEnd = $start->copy()->subDay()->endOfDay();
                $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();
                break;

            case 'month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                $previousStart = Carbon::now()->subMonth()->startOfMonth();
                $previousEnd = Carbon::now()->subMonth()->endOfMonth();
                break;
        }

        return [$start, $end, $previousStart, $previousEnd];
    }

    private function percentageChange($old, $new)
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }

        return round((($new - $old) / abs($old)) * 100, 1);
    }

    /**
     * Sales, expenses and profit per bucket (hour-free: day, or month for long ranges).
     * Buckets are built in PHP so empty days still appear as zero, and it stays
     * database-agnostic (MySQL / SQLite).
     */
    private function getTrend(Carbon $start, Carbon $end, string $range): array
    {
        // A single day would be one dot, so "Today" shows the trailing 7 days instead.
        if ($range === 'today') {
            $end = Carbon::today()->endOfDay();
            $start = Carbon::today()->subDays(6)->startOfDay();
        }

        $monthly  = $range === 'year' || $start->diffInDays($end) > 92;
        $keyFmt   = $monthly ? 'Y-m' : 'Y-m-d';
        $labelFmt = $monthly
            ? ($range === 'year' ? 'M' : 'M y')
            : (in_array($range, ['week', 'today'], true) ? 'D j' : 'j M');

        $buckets = [];
        $cursor  = $monthly ? $start->copy()->startOfMonth() : $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $buckets[$cursor->format($keyFmt)] = [
                'label' => $cursor->format($labelFmt), 'sales' => 0.0, 'cost' => 0.0, 'expenses' => 0.0,
            ];
            $monthly ? $cursor->addMonthNoOverflow() : $cursor->addDay();
        }

        $sales = Sale::whereBetween('sold_at', [$start, $end])
            ->where('status', '!=', 'cancelled')->get(['sold_at', 'total']);
        foreach ($sales as $row) {
            $key = $row->sold_at->format($keyFmt);
            if (isset($buckets[$key])) {
                $buckets[$key]['sales'] += (float) $row->total;
            }
        }

        $costs = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sold_at', [$start, $end])
            ->where('sales.status', '!=', 'cancelled')
            ->get(['sales.sold_at', DB::raw('(sale_items.quantity * sale_items.unit_cost) + sale_items.extra_cost as cost')]);
        foreach ($costs as $row) {
            $key = Carbon::parse($row->sold_at)->format($keyFmt);
            if (isset($buckets[$key])) {
                $buckets[$key]['cost'] += (float) $row->cost;
            }
        }

        $expenses = Expense::whereBetween('expense_date', [$start, $end])->get(['expense_date', 'amount']);
        foreach ($expenses as $row) {
            $key = $row->expense_date->format($keyFmt);
            if (isset($buckets[$key])) {
                $buckets[$key]['expenses'] += (float) $row->amount;
            }
        }

        $b = collect($buckets)->values();

        return [
            'labels'   => $b->pluck('label')->all(),
            'sales'    => $b->map(fn ($x) => round($x['sales'], 2))->all(),
            'expenses' => $b->map(fn ($x) => round($x['expenses'], 2))->all(),
            'profit'   => $b->map(fn ($x) => round($x['sales'] - $x['cost'] - $x['expenses'], 2))->all(),
        ];
    }

    /**
     * Alerts that exist today. Printing-job, printer and order alerts will be added
     * here once those modules exist (see the TODO markers).
     */
    private function buildAlerts(int $out, int $low, int $unpaidCount, float $receivables, int $overdue): array
    {
        $alerts = [];

        if ($out > 0) {
            $names = Product::where('current_stock', '<=', 0)->limit(3)->pluck('name')->implode(', ');
            $alerts[] = [
                'level' => 'danger', 'icon' => 'box', 'route' => 'products.index',
                'title' => $out . ' ' . ($out === 1 ? 'product is' : 'products are') . ' out of stock',
                'detail' => $names,
            ];
        }

        if ($low > 0) {
            $names = Product::where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<=', 'min_stock_level')->limit(3)->pluck('name')->implode(', ');
            $alerts[] = [
                'level' => 'warn', 'icon' => 'alert', 'route' => 'products.index',
                'title' => $low . ' ' . ($low === 1 ? 'product is' : 'products are') . ' running low',
                'detail' => $names,
            ];
        }

        if ($unpaidCount > 0) {
            $alerts[] = [
                'level' => 'warn', 'icon' => 'file', 'route' => 'sales.index',
                'title' => $unpaidCount . ' unpaid ' . ($unpaidCount === 1 ? 'invoice' : 'invoices'),
                'detail' => null,
                'amount' => $receivables,
            ];
        }

        if ($overdue > 0) {
            $alerts[] = [
                'level' => 'danger', 'icon' => 'bell', 'route' => 'sales.index',
                'title' => 'Payment reminder: ' . $overdue . ' ' . ($overdue === 1 ? 'invoice is' : 'invoices are') . ' over 30 days old',
                'detail' => 'Follow up with these customers',
            ];
        }

        // TODO(jobs):     failed printing jobs   -> PrintingJob::where('status', 'failed')
        // TODO(printers): printers in maintenance / error state
        // TODO(orders):   pending orders

        return $alerts;
    }
}