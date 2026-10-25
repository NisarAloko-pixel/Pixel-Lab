<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsOverviewMonthsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $months) {}

    public function title(): string
    {
        return 'Monthly Trend';
    }

    public function headings(): array
    {
        return ['Month', 'Revenue', 'Expenses', 'Net Profit'];
    }

    public function collection(): Collection
    {
        return $this->months->map(fn ($m) => [
            $m['label'],
            number_format($m['revenue'], 2),
            number_format($m['expenses'], 2),
            number_format($m['net_profit'], 2),
        ]);
    }
}