<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsOverviewProductsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Collection $topProducts) {}

    public function title(): string
    {
        return 'Top Products';
    }

    public function headings(): array
    {
        return ['Product', 'Profit'];
    }

    public function collection(): Collection
    {
        return $this->topProducts->map(fn ($p) => [
            $p->name,
            number_format($p->profit, 2),
        ]);
    }
}