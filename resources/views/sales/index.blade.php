<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Sales</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">All recorded orders and their payment status.</p>
            </div>
            <a href="{{ route('sales.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md"
                style="background:var(--accent); color:#FFF;">
                 New sale
            </a>
        </div>

        {{-- Filter bar --}}
        <form method="GET" class="rounded-lg p-4 mb-6 flex flex-wrap items-end gap-3" style="background:var(--surface); border:1px solid var(--line);">
    <div>
        <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">From</label>
        <input type="date" name="from" value="{{ request('from') }}"
            class="rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
    </div>
    <div>
        <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">To</label>
        <input type="date" name="to" value="{{ request('to') }}"
            class="rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
    </div>
    <div>
        <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Payment</label>
        <select name="payment_status"
            class="rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
            <option value="">All payments</option>
            <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
            <option value="partial" @selected(request('payment_status') === 'partial')>Partial</option>
            <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
        </select>
    </div>
    <button class="text-sm font-medium px-4 py-2 rounded-md" style="background:var(--ink); color:var(--paper);">
        Filter
    </button>
    @if (request('from') || request('to') || request('payment_status'))
        <a href="{{ route('sales.index') }}" class="text-sm font-medium px-2 py-2" style="color:var(--ink-soft);">
            Clear
        </a>
    @endif
</form>
        {{-- Table --}}
        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Invoice</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Product</th>
                        <th class="px-4 py-3 font-medium text-center" style="color:var(--ink-soft);">Qty</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Customer</th>
                        <th class="px-4 py-3 font-medium text-center" style="color:var(--ink-soft);">Total</th>
                        <th class="px-4 py-3 font-medium text-center" style="color:var(--ink-soft);">Paid</th>
                        <th class="px-4 py-3 font-medium text-center" style="color:var(--ink-soft);">Balance</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                        <th class="px-4 py-3 font-medium text-center" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        @php
                            $paymentStatus = strtolower($sale->payment_status);
                            $isFreeSale = $sale->is_free;

                            // A fully free invoice shows "Free" instead of its payment status
                            $badgeStyle = $isFreeSale
                                ? 'background:var(--accent-soft); color:var(--accent-ink);'
                                : match($paymentStatus) {
                                    'paid' => 'background:var(--success-soft); color:var(--success);',
                                    'partial' => 'background:var(--warn-soft); color:var(--warn);',
                                    default => 'background:var(--danger-soft); color:var(--danger);',
                                };
                            $badgeLabel = $isFreeSale ? 'Free' : ucfirst($sale->payment_status);
                        @endphp
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3">
                                <a href="{{ route('sales.show', $sale) }}" class="font-medium num" style="color:var(--accent-ink);">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 num" style="color:var(--ink-soft); white-space:nowrap;">{{ $sale->sold_at->timezone($displayTimezone)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                @forelse ($sale->items->take(3) as $item)
                                    @php $itemName = $item->product->name ?? $item->description ?? 'Custom item'; @endphp
                                    <div class="text-xs leading-snug truncate max-w-[180px]" title="{{ $itemName }}">{{ $itemName }}</div>
                                @empty
                                    <span style="color:var(--ink-soft);">&mdash;</span>
                                @endforelse

                                @if ($sale->items->count() > 3)
                                    <div class="text-xs" style="color:var(--ink-soft);">+{{ $sale->items->count() - 3 }} more</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center num">
                                @forelse ($sale->items->take(3) as $item)
                                    <div class="text-xs leading-snug">{{ $item->quantity + 0 }}</div>
                                @empty
                                    <span style="color:var(--ink-soft);">&mdash;</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-center num font-medium">{{ number_format($sale->total, 2) }}</td>
                            <td class="px-4 py-3 text-center num" style="color:var(--ink-soft);">{{ number_format($sale->amount_paid, 2) }}</td>
                            <td class="px-4 py-3 text-center num font-medium" style="color:{{ $sale->balance > 0 ? 'var(--danger)' : 'var(--ink)' }};">
                                {{ number_format($sale->balance, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge" style="{{ $badgeStyle }}">{{ $badgeLabel }}</span>
                             <span class="badge mt-1" style="{{ strtolower($sale->status) === 'cancelled' ? 'background:var(--danger-soft); color:var(--danger);' : 'background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);' }}">{{ ucfirst($sale->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
    <div class="flex justify-end items-center gap-1">
     <button type="button" data-invoice-url="{{ route('sales.invoice', $sale) }}"
    class="p-1.5 rounded-md hover:opacity-70" style="color:var(--ink-soft);" title="Print invoice">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 6 2 18 2 18 9"/>
        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
        <rect width="12" height="8" x="6" y="14"/>
    </svg>
</button>

        <a href="{{ route('sales.show', $sale) }}"
            class="p-1.5 rounded-md hover:opacity-70" style="color:var(--ink-soft);" title="View">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </a>
        <a href="{{ route('sales.edit', $sale) }}"
            class="p-1.5 rounded-md hover:opacity-70" style="color:var(--accent-ink);" title="Edit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
            </svg>
        </a>
        @if (strtolower($sale->status) !== 'cancelled')
            <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="cancel-sale-form">
                @csrf
                <input type="hidden" name="reason">
                <button type="submit" class="p-1.5 rounded-md hover:opacity-70" style="color:var(--warn);" title="Cancel this sale">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="m15 9-6 6M9 9l6 6"/>
                    </svg>
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('sales.destroy', $sale) }}"
            onsubmit="return confirm('Delete invoice {{ $sale->invoice_number }}? This cannot be undone.');">
            @csrf @method('DELETE')
            <button type="submit" class="p-1.5 rounded-md hover:opacity-70" style="color:var(--danger);" title="Delete">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/>
                    <path d="M10 11v6M14 11v6"/>
                </svg>
            </button>
        </form>
    </div>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No sales recorded yet — start with your first order.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $sales->links() }}</div>
    </div>
</div>

@include('sales._invoice-modal')
@include('sales._cancel-modal')

</x-layouts.pixel>