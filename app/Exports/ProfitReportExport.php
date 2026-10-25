<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitReportExport implements Export, FromCollection, WithHeadings
{
    public function __construct(private array $data) {}

    public function headings(): array
    {
        return ['Metric', 'Current Period', 'Prior Period', 'Change'];
    }

    public function collection(): Collection
    {
        $s = $this->data['summary'];
        $p = $this->data['previousSummary'];
        $c = $this->data['comparison'];

        return collect([
            ['Revenue', number_format($s['revenue'], 2), number_format($p['revenue'], 2), $c['revenue'] === null ? '—' : number_format($c['revenue'], 1).'%'],
            ['Gross Profit', number_format($s['gross_profit'], 2), number_format($p['gross_profit'], 2), $c['gross_profit'] === null ? '—' : number_format($c['gross_profit'], 1).'%'],
            ['Net Profit', number_format($s['net_profit'], 2), number_format($p['net_profit'], 2), $c['net_profit'] === null ? '—' : number_format($c['net_profit'], 1).'%'],
            ['Profit Margin %', number_format($s['profit_margin_pct'], 1), number_format($p['profit_margin_pct'], 1), number_format($c['profit_margin_pct'], 1).' pts'],
            ['COGS', number_format($s['cogs'], 2), '', ''],
            ['Operating Expenses', number_format($s['operating_expenses'], 2), '', ''],
            ['Sales Count', $s['sales_count'], '', ''],
            ['Avg Order Value', number_format($s['average_order_value'], 2), '', ''],
        ]);
    }
}