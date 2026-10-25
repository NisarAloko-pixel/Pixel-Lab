<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsOverviewExport implements Export, WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        return [
            'Summary' => new ReportsOverviewSummarySheet($this->data),
            'Monthly Trend' => new ReportsOverviewMonthsSheet($this->data['months']),
            'Expense Breakdown' => new ReportsOverviewExpensesSheet($this->data['expenseBreakdown']),
            'Top Products' => new ReportsOverviewProductsSheet($this->data['topProducts']),
        ];
    }
}