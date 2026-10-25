<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesReportExport;
use App\Exports\ProductProfitReportExport;
use App\Exports\ProfitReportExport;
use App\Exports\PurchasesReportExport;
use App\Exports\ReportsOverviewExport;
use App\Exports\SalesReportExport;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\FinancialService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

/**
 * EXTENSIONS IN THIS FILE (vs. your original)
 * --------------------------------------------
 * Everything under buildIndexData() up to $topProducts is unchanged —
 * still goes through $this->financial->summary() and
 * ->productProfitReport() exactly as before.
 *
 * New keys added to the index payload: comparisons, granularity,
 * trend (chart series independent of the fixed trailingMonths(6)),
 * expenseTrend, salesAnalysis, profitAnalysis, customerAnalysis,
 * inventory, payments, recentTransactions, purchases.
 *
 * Now wired against the real FinancialService:
 *  - summary()'s numeric fields are bcmath strings, so every value
 *    pulled from it is explicitly (float)-cast before arithmetic or
 *    comparison (bcsub/bcdiv results don't auto-coerce reliably in
 *    strict comparisons the way plain numeric strings do).
 *  - totalReceivables() and inventoryValue() are the single source
 *    of truth per the service's own doc comment, so the Receivables
 *    KPI and inventory cost/value figures call through them instead
 *    of re-summing sales/products here.
 *  - productProfitReport() returns quantity_sold (not units_sold)
 *    and has NO category column — it only joins sales+products. The
 *    "best category" / top-products category column below run a
 *    small separate lookup against `products`/`categories` rather
 *    than assuming the service provides it.
 *  - Sale filtering elsewhere in this file (salesAnalysis, payments,
 *    customerAnalysis, recentTransactions) intentionally still spans
 *    ALL sale statuses, not just "completed" — those sections report
 *    order pipeline / collections activity, which is a different
 *    question from the service's revenue formula. Only the Financial
 *    Summary table's Gross Sales/Discounts/Tax row is now scoped to
 *    status=completed, to reconcile with summary()'s Net Revenue.
 *
 * Customer analysis assumes an App\Models\Customer model exists
 * (Sale already has a `customer` relation, so this should hold) with
 * `name` and `created_at` — adjust if your actual columns differ.
 *
 * PURCHASES: each `purchases` row is ONE purchased item (supply or
 * filament spool) with its own total_price / shipping_cost / status.
 * They are reported separately and never counted as expenses, so
 * nothing here feeds FinancialService::summary(). Cancelled purchases
 * are excluded. amount_paid is stored in base currency.
 */
class ReportController extends Controller
{
    public function __construct(private FinancialService $financial) {}

    private function resolveRange(Request $request): array
    {
        $range = $request->input("range", "this_month");
        $today = Carbon::today();

        return match ($range) {
            "today" => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            "yesterday" => [$today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay()],
            "this_week" => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            "this_month" => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            "last_month" => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            "this_year" => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            "custom" => [
                Carbon::parse($request->input("from", $today->toDateString()))->startOfDay(),
                Carbon::parse($request->input("to", $today->toDateString()))->endOfDay(),
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };
    }

    private function trailingMonths(int $count): \Illuminate\Support\Collection
    {
        return collect(range($count - 1, 0))->map(function ($i) {
            $date = Carbon::today()->subMonthsNoOverflow($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $s = $this->financial->summary($start, $end);

            return [
                'label' => $date->format('M'),
                'revenue' => $s['revenue'],
                'expenses' => $s['cogs'] + $s['operating_expenses'],
                'net_profit' => $s['net_profit'],
            ];
        });
    }

    private function previousPeriod(Carbon $from, Carbon $to): array
    {
        $days = $from->diffInDays($to) + 1;

        return [
            $from->copy()->subDays($days)->startOfDay(),
            $from->copy()->subDay()->endOfDay(),
        ];
    }

    private function pctChange(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current == 0.0 ? 0.0 : null;
        }

        return (($current - $previous) / abs($previous)) * 100;
    }

    /* -----------------------------------------------------------
     | Overview (index) — extended
     |------------------------------------------------------------*/

    private function buildIndexData(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);
        $summary = $this->financial->summary($from, $to);
        $months = $this->trailingMonths(6);

        [$prevFrom, $prevTo] = $this->previousPeriod($from, $to);
        $previousSummary = $this->financial->summary($prevFrom, $prevTo);

        $comparisons = [
            'revenue' => $this->pctChange((float) $summary['revenue'], (float) $previousSummary['revenue']),
            'net_profit' => $this->pctChange((float) $summary['net_profit'], (float) $previousSummary['net_profit']),
            'expenses' => $this->pctChange(
                (float) $summary['cogs'] + (float) $summary['operating_expenses'],
                (float) $previousSummary['cogs'] + (float) $previousSummary['operating_expenses']
            ),
        ];

        $expenseBreakdown = Expense::with('category')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->groupBy(fn ($e) => $e->category->name ?? 'Uncategorized')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc()
            ->take(6);

        $granularity = $request->input('granularity', 'monthly');
        $expenseTrend = $this->expenseTrend($from, $to, $granularity);
        $trend = $this->trendSeries($from, $to, $granularity);

        $allProductRows = collect($this->financial->productProfitReport($from, $to));

        // productProfitReport() only joins sales+products, so it carries no
        // category. Enrich with a small separate lookup rather than adding
        // a join to the shared financial formula query.
        if ($allProductRows->isNotEmpty()) {
            $categoryByProduct = Schema::hasTable('categories')
                ? Product::whereIn('products.id', $allProductRows->pluck('id'))
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->pluck('categories.name', 'products.id')
                : collect();

            $allProductRows = $allProductRows->map(function ($row) use ($categoryByProduct) {
                $row->category = $categoryByProduct[$row->id] ?? '—';
                return $row;
            });
        }

        $topProductsBy = $request->input('top_products_by', 'profit');
        $topProducts = $allProductRows
            ->sortByDesc(match ($topProductsBy) {
                'revenue' => 'revenue',
                'units' => 'quantity_sold',
                default => 'profit',
            })
            ->take(10)
            ->values();

        // Financial Summary section (spec 4) needs gross_sales/discounts/tax,
        // which aren't in FinancialService::summary()'s known keys — pulled
        // straight from the sales table's own subtotal/discount/tax columns,
        // scoped to status=completed so it reconciles with summary()'s
        // "Net Revenue" (sum of total for completed sales).
        $salesTotals = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(subtotal),0) as gross_sales, COALESCE(SUM(discount),0) as discounts, COALESCE(SUM(tax),0) as tax')
            ->first();

        $salesAnalysis = $this->salesAnalysis($from, $to);
        $profitAnalysis = [
            'revenue' => (float) $summary['revenue'],
            'cost' => (float) $summary['cogs'],
            'gross_profit' => (float) $summary['gross_profit'],
            'expenses' => (float) $summary['operating_expenses'],
            'net_profit' => (float) $summary['net_profit'],
            'profit_margin_pct' => (float) $summary['profit_margin_pct'],
        ];

        $customerAnalysis = $this->customerAnalysis($from, $to);
        $inventory = $this->inventoryReport();
        $payments = $this->paymentsSummary($from, $to);
        $purchases = $this->purchasesSummary($from, $to);
        $recentPurchases = $this->recentPurchaseRows(8);
        $totalReceivables = (float) $this->financial->totalReceivables();
        $recentTransactions = Sale::with('customer')
            ->latest('sold_at')
            ->limit(15)
            ->get();

        return compact(
            'summary', 'from', 'to', 'months', 'expenseBreakdown', 'topProducts',
            'comparisons', 'granularity', 'expenseTrend', 'topProductsBy',
            'salesAnalysis', 'profitAnalysis', 'customerAnalysis',
            'inventory', 'payments', 'recentTransactions', 'salesTotals', 'trend',
            'totalReceivables', 'purchases', 'recentPurchases'
        );
    }

    private function expenseTrend(Carbon $from, Carbon $to, string $granularity): array
    {
        $buckets = match ($granularity) {
            'daily' => collect(\Carbon\CarbonPeriod::create($from, $to))
                ->map(fn ($d) => [$d->copy()->startOfDay(), $d->copy()->endOfDay(), $d->format('M j')]),
            'weekly' => (function () use ($from, $to) {
                $out = collect();
                $cursor = $from->copy()->startOfWeek();
                while ($cursor->lte($to)) {
                    $out->push([$cursor->copy(), $cursor->copy()->endOfWeek(), 'Wk '.$cursor->format('M j')]);
                    $cursor = $cursor->addWeek();
                }
                return $out;
            })(),
            'yearly' => (function () use ($from, $to) {
                $out = collect();
                $cursor = $from->copy()->startOfYear();
                while ($cursor->lte($to)) {
                    $out->push([$cursor->copy(), $cursor->copy()->endOfYear(), $cursor->format('Y')]);
                    $cursor = $cursor->addYear();
                }
                return $out;
            })(),
            default => (function () use ($from, $to) {
                $out = collect();
                $cursor = $from->copy()->startOfMonth();
                while ($cursor->lte($to)) {
                    $out->push([$cursor->copy(), $cursor->copy()->endOfMonth(), $cursor->format('M Y')]);
                    $cursor = $cursor->addMonthNoOverflow();
                }
                return $out;
            })(),
        };

        $labels = [];
        $values = [];
        foreach ($buckets as [$bFrom, $bTo, $label]) {
            $labels[] = $label;
            $values[] = (float) Expense::whereBetween('expense_date', [$bFrom, $bTo])->sum('amount');
        }

        return compact('labels', 'values');
    }

    /**
     * General revenue/cost/expenses/profit series for the main chart,
     * bucketed by the selected granularity (spec section 3's daily/
     * weekly/monthly/yearly toggle). $months (trailingMonths) stays
     * as-is for anything else that already depends on its shape.
     */
    private function trendSeries(Carbon $from, Carbon $to, string $granularity): array
    {
        $buckets = match ($granularity) {
            'daily' => collect(\Carbon\CarbonPeriod::create($from, $to))
                ->map(fn ($d) => [$d->copy()->startOfDay(), $d->copy()->endOfDay(), $d->format('M j')]),
            'weekly' => (function () use ($from, $to) {
                $out = collect();
                $cursor = $from->copy()->startOfWeek();
                while ($cursor->lte($to)) {
                    $out->push([$cursor->copy(), $cursor->copy()->endOfWeek(), 'Wk '.$cursor->format('M j')]);
                    $cursor = $cursor->addWeek();
                }
                return $out;
            })(),
            'yearly' => (function () use ($from, $to) {
                $out = collect();
                $cursor = $from->copy()->startOfYear();
                while ($cursor->lte($to)) {
                    $out->push([$cursor->copy(), $cursor->copy()->endOfYear(), $cursor->format('Y')]);
                    $cursor = $cursor->addYear();
                }
                return $out;
            })(),
            default => null, // monthly falls back to trailingMonths(6) below
        };

        if ($buckets === null) {
            return $this->trailingMonths(6)->map(fn ($m) => [
                'label' => $m['label'],
                'revenue' => $m['revenue'],
                'expenses' => $m['expenses'],
                'net_profit' => $m['net_profit'],
            ])->values()->all();
        }

        return $buckets->map(function ($bucket) {
            [$bFrom, $bTo, $label] = $bucket;
            $s = $this->financial->summary($bFrom, $bTo);

            return [
                'label' => $label,
                'revenue' => (float) $s['revenue'],
                'expenses' => (float) $s['cogs'] + (float) $s['operating_expenses'],
                'net_profit' => (float) $s['net_profit'],
            ];
        })->values()->all();
    }

    private function salesAnalysis(Carbon $from, Carbon $to): array
    {
        $sales = Sale::whereBetween('sold_at', [$from, $to])->get(['id', 'status', 'total']);

        $items = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$from, $to]));

        $bestProduct = (clone $items)
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->with('product:id,name')
            ->first();

        $bestCategory = null;
        if (Schema::hasTable('categories')) {
            $bestCategory = (clone $items)
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('categories.name', DB::raw('SUM(sale_items.line_total) as revenue'))
                ->groupBy('categories.name')
                ->orderByDesc('revenue')
                ->first();
        }

        $totalOrders = $sales->count();

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $sales->where('status', 'completed')->count(),
            'pending_orders' => $sales->where('status', 'pending')->count(),
            'cancelled_orders' => $sales->where('status', 'cancelled')->count(),
            'average_order_value' => $totalOrders > 0 ? $sales->sum('total') / $totalOrders : 0.0,
            'total_items_sold' => (int) (clone $items)->sum('quantity'),
            'best_selling_product' => $bestProduct->product->name ?? null,
            'best_performing_category' => $bestCategory->name ?? null,
        ];
    }

    private function customerAnalysis(Carbon $from, Carbon $to): array
    {
        if (! Schema::hasTable('customers')) {
            return [
                'total_customers' => 0, 'new_customers' => 0, 'returning_customers' => 0,
                'with_balance' => 0, 'top_by_sales' => collect(),
            ];
        }

        $totalCustomers = DB::table('customers')->count();
        $newCustomers = DB::table('customers')->whereBetween('created_at', [$from, $to])->count();

        $customersInPeriod = Sale::whereBetween('sold_at', [$from, $to])->distinct('customer_id')->pluck('customer_id');
        $returning = Sale::whereIn('customer_id', $customersInPeriod)
            ->where('sold_at', '<', $from)
            ->distinct('customer_id')->count('customer_id');

        $withBalance = Sale::where('balance', '>', 0)->distinct('customer_id')->count('customer_id');

        $topBySales = Sale::whereBetween('sold_at', [$from, $to])
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select('customers.id', 'customers.name', DB::raw('SUM(sales.total) as total_sales'))
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_sales')
            ->limit(5)->get();

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returning,
            'with_balance' => $withBalance,
            'top_by_sales' => $topBySales,
        ];
    }

    private function inventoryReport(): array
    {
        $products = Product::get(['id', 'name', 'sku', 'current_stock', 'min_stock_level', 'cost_price', 'selling_price']);

        $lowStock = $products->filter(fn ($p) => $p->current_stock > 0 && $p->current_stock <= $p->min_stock_level);
        $outOfStock = $products->filter(fn ($p) => $p->current_stock <= 0);

        // Cost/value totals come from FinancialService::inventoryValue() — the
        // single source of truth — rather than re-summing here.
        $values = $this->financial->inventoryValue();

        return [
            'total_products' => $products->count(),
            'total_inventory_cost' => (float) $values['cost_value'],
            'total_inventory_value' => (float) $values['selling_value'],
            'low_stock_count' => $lowStock->count(),
            'out_of_stock_count' => $outOfStock->count(),
            'low_stock_products' => $lowStock->values(),
            'out_of_stock_products' => $outOfStock->values(),
        ];
    }

    private function paymentsSummary(Carbon $from, Carbon $to): array
    {
        $sales = Sale::whereBetween('sold_at', [$from, $to])->get(['id', 'total', 'amount_paid', 'balance']);

        $fullyPaid = $sales->filter(fn ($s) => $s->total > 0 && $s->amount_paid >= $s->total)->count();
        $unpaid = $sales->filter(fn ($s) => $s->amount_paid <= 0)->count();
        $partial = $sales->count() - $fullyPaid - $unpaid;

        $totalSales = (float) $sales->sum('total');
        $totalPaid = (float) $sales->sum('amount_paid');

        return [
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_outstanding' => (float) $sales->sum('balance'),
            'collection_pct' => $totalSales > 0 ? ($totalPaid / $totalSales) * 100 : 0.0,
            'fully_paid_orders' => $fullyPaid,
            'partially_paid_orders' => $partial,
            'unpaid_orders' => $unpaid,
        ];
    }

    /* -----------------------------------------------------------
     | Purchases (separate from expenses)
     |------------------------------------------------------------*/

    /** Turns one purchases row into the report row (all amounts in base currency). */
    private function mapPurchase(Purchase $p): object
    {
        $rate  = (float) ($p->exchange_rate ?: 1);
        $total = (float) $p->total_cost_base;              // (price + shipping) x rate
        $paid  = min((float) $p->amount_paid, $total);     // amount_paid is already base currency
        $due   = (float) $p->amount_due_base;
        $price = (float) $p->total_price * $rate;

        return (object) [
            'purchase' => $p,
            'name'     => $p->displayName(),
            'total'    => $total,
            'paid'     => $paid,
            'due'      => $due,
            'shipping' => (float) $p->shipping_cost * $rate,
            'filament' => $p->item_type === 'filament' ? $price : 0.0,
            'supplies' => $p->item_type === 'supply' ? $price : 0.0,
        ];
    }

    /** Cancelled purchases never count. */
    private function activePurchases()
    {
        return Purchase::with(['supplier', 'supply'])
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'cancelled'));
    }

    /** Purchases inside a date range, newest first. */
    private function purchaseRows(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return $this->activePurchases()
            ->where(function ($q) use ($from, $to) {
                $q->where(function ($q2) use ($from, $to) {
                    $q2->whereDate('order_date', '>=', $from->toDateString())
                       ->whereDate('order_date', '<=', $to->toDateString());
                })->orWhere(function ($q2) use ($from, $to) {
                    // saved without an order date: count by the day it was created
                    $q2->whereNull('order_date')
                       ->whereDate('created_at', '>=', $from->toDateString())
                       ->whereDate('created_at', '<=', $to->toDateString());
                });
            })
            ->orderByRaw('COALESCE(order_date, created_at) DESC')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($p) => $this->mapPurchase($p));
    }

    /** Latest purchases regardless of the selected date range (for the overview card). */
    private function recentPurchaseRows(int $limit = 8): \Illuminate\Support\Collection
    {
        return $this->activePurchases()
            ->orderByRaw('COALESCE(order_date, created_at) DESC')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => $this->mapPurchase($p));
    }

    private function purchasesSummary(Carbon $from, Carbon $to): array
    {
        $rows = $this->purchaseRows($from, $to);

        return [
            'rows'           => $rows,
            'count'          => $rows->count(),
            'total'          => $rows->sum('total'),
            'shipping'       => $rows->sum('shipping'),
            'paid'           => $rows->sum('paid'),
            'payable'        => $rows->sum('due'),
            // what you owe suppliers right now, whatever the date filter (like Receivables)
            'payable_all'    => $this->activePurchases()->get()->sum(fn ($p) => (float) $p->amount_due_base),
            'filament_total' => $rows->sum('filament'),
            'supplies_total' => $rows->sum('supplies'),
            'by_supplier'    => $rows
                ->groupBy(fn ($r) => $r->purchase->supplier->name ?? 'No supplier')
                ->map(fn ($g) => $g->sum('total'))
                ->sortDesc(),
        ];
    }

    /** The purchase report shows ALL purchases (newest first) until a date button is chosen. */
    private function buildPurchasesData(Request $request): array
    {
        $range = $request->input('range', 'all');
        $isAll = $range === 'all';

        if ($isAll) {
            $from = Carbon::create(2000, 1, 1)->startOfDay();
            $to   = Carbon::today()->addYears(10)->endOfDay();   // also catches future-dated orders
        } else {
            [$from, $to] = $this->resolveRange($request);
        }

        $purchases = $this->purchasesSummary($from, $to);

        return compact('purchases', 'from', 'to', 'range', 'isAll');
    }

    /* -----------------------------------------------------------
     | Data builders for the detail reports
     |------------------------------------------------------------*/

    private function buildSalesData(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);
        $sales = Sale::with(["customer", "items"])
            ->where("status", "completed")
            ->whereBetween("sold_at", [$from, $to])
            ->latest("sold_at")
            ->get();

        return compact('sales', 'from', 'to');
    }

    private function buildProfitData(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);
        $summary = $this->financial->summary($from, $to);

        [$prevFrom, $prevTo] = $this->previousPeriod($from, $to);
        $previousSummary = $this->financial->summary($prevFrom, $prevTo);

        $comparison = [
            'revenue' => $this->pctChange($summary['revenue'], $previousSummary['revenue']),
            'gross_profit' => $this->pctChange($summary['gross_profit'], $previousSummary['gross_profit']),
            'net_profit' => $this->pctChange($summary['net_profit'], $previousSummary['net_profit']),
            'profit_margin_pct' => $summary['profit_margin_pct'] - $previousSummary['profit_margin_pct'],
        ];

        $months = $this->trailingMonths(6);

        return compact('summary', 'previousSummary', 'comparison', 'from', 'to', 'months', 'prevFrom', 'prevTo');
    }

    private function buildProductProfitData(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->financial->productProfitReport($from, $to);

        return compact('rows', 'from', 'to');
    }

    private function buildExpensesData(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);
        $expenses = Expense::with("category")->whereBetween("expense_date", [$from, $to])->latest("expense_date")->get();

        return compact('expenses', 'from', 'to');
    }

    /* -----------------------------------------------------------
     | Public actions
     |------------------------------------------------------------*/

    public function index(Request $request)
    {
        return view('reports.index', $this->buildIndexData($request));
    }

    public function exportIndexPdf(Request $request)
    {
        $data = $this->buildIndexData($request);
        $pdf = Pdf::loadView('reports.pdf.index', $data);

        return $pdf->download('report-overview-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.pdf');
    }

    public function exportIndexExcel(Request $request)
    {
        $data = $this->buildIndexData($request);

        return Excel::download(
            new ReportsOverviewExport($data),
            'report-overview-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.xlsx'
        );
    }

    public function exportIndexCsv(Request $request)
    {
        $data = $this->buildIndexData($request);

        return Excel::download(
            new ReportsOverviewExport($data),
            'report-overview-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function sales(Request $request)
    {
        return view("reports.sales", $this->buildSalesData($request));
    }

    public function exportSalesPdf(Request $request)
    {
        $data = $this->buildSalesData($request);
        $pdf = Pdf::loadView('reports.pdf.sales', $data);

        return $pdf->download('sales-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.pdf');
    }

    public function exportSalesExcel(Request $request)
    {
        $data = $this->buildSalesData($request);

        return Excel::download(
            new SalesReportExport($data['sales']),
            'sales-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.xlsx'
        );
    }

    public function profit(Request $request)
    {
        return view("reports.profit", $this->buildProfitData($request));
    }

    public function exportProfitPdf(Request $request)
    {
        $data = $this->buildProfitData($request);
        $pdf = Pdf::loadView('reports.pdf.profit', $data);

        return $pdf->download('profit-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.pdf');
    }

    public function exportProfitExcel(Request $request)
    {
        $data = $this->buildProfitData($request);

        return Excel::download(
            new ProfitReportExport($data),
            'profit-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.xlsx'
        );
    }

    public function productProfit(Request $request)
    {
        return view("reports.product-profit", $this->buildProductProfitData($request));
    }

    public function exportProductProfitPdf(Request $request)
    {
        $data = $this->buildProductProfitData($request);
        $pdf = Pdf::loadView('reports.pdf.product-profit', $data);

        return $pdf->download('product-profit-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.pdf');
    }

    public function exportProductProfitExcel(Request $request)
    {
        $data = $this->buildProductProfitData($request);

        return Excel::download(
            new ProductProfitReportExport(collect($data['rows'])),
            'product-profit-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.xlsx'
        );
    }

    public function expenses(Request $request)
    {
        return view("reports.expenses", $this->buildExpensesData($request));
    }

    public function exportExpensesPdf(Request $request)
    {
        $data = $this->buildExpensesData($request);
        $pdf = Pdf::loadView('reports.pdf.expenses', $data);

        return $pdf->download('expenses-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.pdf');
    }

    public function exportExpensesExcel(Request $request)
    {
        $data = $this->buildExpensesData($request);

        return Excel::download(
            new ExpensesReportExport($data['expenses']),
            'expenses-report-'.$data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d').'.xlsx'
        );
    }

    public function purchases(Request $request)
    {
        return view('reports.purchases', $this->buildPurchasesData($request));
    }

    public function exportPurchasesPdf(Request $request)
    {
        $data = $this->buildPurchasesData($request);
        $pdf = Pdf::loadView('reports.pdf.purchases', $data);

        $label = $data['isAll'] ? 'all' : $data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d');

        return $pdf->download('purchases-report-'.$label.'.pdf');
    }

    public function exportPurchasesExcel(Request $request)
    {
        $data = $this->buildPurchasesData($request);

        return Excel::download(
            new PurchasesReportExport($data['purchases']['rows']),
            'purchases-report-'.($data['isAll'] ? 'all' : $data['from']->format('Y-m-d').'-to-'.$data['to']->format('Y-m-d')).'.xlsx'
        );
    }
}