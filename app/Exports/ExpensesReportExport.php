<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesReportExport implements Export, FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $expenses) {}

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return ['Date', 'Category', 'Description', 'Amount', 'Method'];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_date->format('Y-m-d'),
            $expense->category->name ?? 'Uncategorized',
            $expense->description,
            number_format($expense->amount, 2),
            ucfirst(str_replace('_', ' ', $expense->payment_method)),
        ];
    }
}