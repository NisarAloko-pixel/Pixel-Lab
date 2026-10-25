<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/** Expects the rows from ReportController::purchaseRows() (amounts in base currency). */
class PurchasesReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['ID', 'Date', 'Item', 'Type', 'Supplier', 'Status', 'Quantity', 'Currency', 'Shipping', 'Total', 'Paid', 'Due'];
    }

    public function map($r): array
    {
        $p = $r->purchase;

        return [
            $p->id,
            Carbon::parse($p->order_date ?? $p->created_at)->format('Y-m-d'),
            $r->name,
            ucfirst($p->item_type),
            $p->supplier->name ?? '',
            ucfirst($p->status ?: 'pending'),
            (float) $p->quantity,
            $p->currency,
            round($r->shipping, 2),
            round($r->total, 2),
            round($r->paid, 2),
            round($r->due, 2),
        ];
    }
}