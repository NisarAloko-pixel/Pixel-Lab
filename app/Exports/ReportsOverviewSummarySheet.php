<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsOverviewSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return ['Period', 'Revenue', 'Net Profit', 'Operating Expenses', 'Profit Margin %'];
    }

    public function collection(): Collection
    {
        $s = $this->data['summary'];

        return collect([[
            $this->data['from']->format('Y-m-d').' to '.$this->data['to']->format('Y-m-d'),
            number_format($s['revenue'], 2),
            number_format($s['net_profit'], 2),
            number_format($s['operating_expenses'], 2),
            number_format($s['profit_margin_pct'], 1),
        ]]);
    }
}