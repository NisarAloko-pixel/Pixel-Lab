<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements Export, FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $sales) {}

    public function collection(): Collection
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'Invoice #', 'Date', 'Customer', 'Items',
            'Subtotal', 'Discount', 'Tax', 'Total',
            'Cost', 'Gross Profit', 'Payment Status', 'Payment Method',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_number,
            optional($sale->sold_at)->format('Y-m-d'),
            $sale->customer->name ?? '—',
            $sale->items->map(fn ($i) => $i->description.' x'.$i->quantity)->implode('; '),
            number_format($sale->subtotal, 2),
            number_format($sale->discount, 2),
            number_format($sale->tax, 2),
            number_format($sale->total, 2),
            number_format($sale->cost_total, 2),
            number_format($sale->gross_profit, 2),
            $sale->payment_status,
            $sale->payment_method,
        ];
    }
}