<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The ONLY place that defines revenue / cost / gross profit / net profit. The
 * dashboard, financial overview, profit report, and product profit report must all
 * call through here rather than re-deriving these formulas, so a formula change
 * only has to happen once and every screen stays consistent with it.
 *
 * Definitions:
 *   Revenue      = sum(sales.total) for completed sales in the period
 *   COGS         = sum(sales.cost_total) for completed sales in the period
 *   Gross Profit = Revenue - COGS
 *   Op. Expenses = sum(expenses.amount) in the period
 *   Net Profit   = Gross Profit - Op. Expenses
 */
class FinancialService
{
    public function summary(Carbon $from, Carbon $to): array
    {
        $sales = Sale::query()
            ->where("status", "completed")
            ->whereBetween("sold_at", [$from, $to])
            ->selectRaw("COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(cost_total), 0) as cogs, COUNT(*) as sales_count")
            ->first();

        $expenses = (string) Expense::query()
            ->whereBetween("expense_date", [$from, $to])
            ->sum("amount");

        $revenue = (string) $sales->revenue;
        $cogs = (string) $sales->cogs;
        $grossProfit = bcsub($revenue, $cogs, 2);
        $netProfit = bcsub($grossProfit, $expenses, 2);

        return [
            "from" => $from->toDateString(),
            "to" => $to->toDateString(),
            "revenue" => $revenue,
            "cogs" => $cogs,
            "gross_profit" => $grossProfit,
            "operating_expenses" => $expenses,
            "net_profit" => $netProfit,
            "profit_margin_pct" => (float) $revenue > 0 ? round(((float) $netProfit / (float) $revenue) * 100, 2) : 0.0,
            "sales_count" => (int) $sales->sales_count,
            "average_order_value" => (int) $sales->sales_count > 0 ? bcdiv($revenue, (string) $sales->sales_count, 2) : "0.00",
        ];
    }

    public function totalReceivables(): string
    {
        return (string) Sale::where("status", "completed")->sum("balance");
    }

    public function totalPayables(): string
    {
        return (string) DB::table("purchases")->whereNull("deleted_at")->sum("balance");
    }

    public function inventoryValue(): array
    {
        $row = DB::table("products")
            ->whereNull("deleted_at")
            ->selectRaw("COALESCE(SUM(current_stock * cost_price), 0) as cost_value, COALESCE(SUM(current_stock * selling_price), 0) as selling_value")
            ->first();

        return [
            "cost_value" => (string) $row->cost_value,
            "selling_value" => (string) $row->selling_value,
        ];
    }

    public function productProfitReport(Carbon $from, Carbon $to)
    {
        return DB::table("sale_items")
            ->join("sales", "sales.id", "=", "sale_items.sale_id")
            ->join("products", "products.id", "=", "sale_items.product_id")
            ->where("sales.status", "completed")
            ->whereBetween("sales.sold_at", [$from, $to])
            ->groupBy("products.id", "products.name")
            ->selectRaw("
    products.id, products.name,
    SUM(sale_items.quantity) as quantity_sold,
    SUM(sale_items.line_total) as revenue,
    SUM(sale_items.quantity * sale_items.unit_cost + sale_items.extra_cost) as cost,
    SUM(sale_items.line_total) - SUM(sale_items.quantity * sale_items.unit_cost + sale_items.extra_cost) as profit
")
            ->orderByDesc("revenue")
            ->get();
    }
}
