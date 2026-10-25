<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">
        <a href="{{ Route::has('reports.index') ? route('reports.index') : url()->previous() }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to reports
        </a>

        <h1 class="ledger-font text-3xl mb-1">Product Profit Report</h1>
        <p class="text-sm mb-6" style="color:var(--ink-soft);">
            {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
        </p>

        <div class="flex gap-2 mb-6">
    <a href="{{ route('reports.product-profit.exportPdf', request()->query()) }}"
        class="text-sm font-semibold px-4 py-2 rounded-md"
        style="border:1px solid var(--line); color:var(--ink-soft);">
        Export PDF
    </a>
    <a href="{{ route('reports.product-profit.exportExcel', request()->query()) }}"
        class="text-sm font-semibold px-4 py-2 rounded-md"
        style="border:1px solid var(--line); color:var(--ink-soft);">
        Export Excel
    </a>
</div>
        <div class="card-3d overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Product</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Qty Sold</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Revenue</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Cost</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Profit</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php $margin = $row->revenue > 0 ? ($row->profit / $row->revenue) * 100 : 0; @endphp
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3 font-medium">{{ $row->name }}</td>
                            <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">{{ $row->quantity_sold }}</td>
                            <td class="px-4 py-3 text-right num">{{ number_format($row->revenue, 2) }}</td>
                            <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">{{ number_format($row->cost, 2) }}</td>
                            <td class="px-4 py-3 text-right num font-medium" style="color:{{ $row->profit >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                {{ number_format($row->profit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right num" style="color:{{ $margin >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                {{ number_format($margin, 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No sales in this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.pixel>
