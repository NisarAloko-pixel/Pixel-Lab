<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductProfitReportExport implements Export, FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Product', 'Qty Sold', 'Revenue', 'Cost', 'Profit', 'Margin %'];
    }

    public function map($row): array
    {
        $margin = $row->revenue > 0 ? ($row->profit / $row->revenue) * 100 : 0;

        return [
            $row->name,
            $row->quantity_sold,
            number_format($row->revenue, 2),
            number_format($row->cost, 2),
            number_format($row->profit, 2),
            number_format($margin, 1),
        ];
    }
}