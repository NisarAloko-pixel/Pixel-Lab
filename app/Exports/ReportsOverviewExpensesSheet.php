<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsOverviewExpensesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $expenseBreakdown) {}

    public function title(): string
    {
        return 'Expense Breakdown';
    }

    public function headings(): array
    {
        return ['Category', 'Amount'];
    }

    public function collection(): Collection
    {
        return $this->expenseBreakdown->map(fn ($amount, $category) => [
            $category,
            number_format($amount, 2),
        ])->values();
    }
}