<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">

        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to customers
        </a>

        <h1 class="ledger-font text-3xl mb-1">{{ $customer->name }}</h1>
        <p class="text-sm mb-8" style="color:var(--ink-soft);">
            {{ $customer->phone }} &middot; {{ $customer->email }}
        </p>

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <div class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Total Purchases</div>
                <div class="ledger-font text-2xl num">{{ number_format($customer->total_purchases, 2) }}</div>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <div class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Total Paid</div>
                <div class="ledger-font text-2xl num">{{ number_format($customer->total_paid, 2) }}</div>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <div class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--ink-soft);">Outstanding Balance</div>
                <div class="ledger-font text-2xl num" style="color:{{ $customer->outstanding_balance > 0 ? 'var(--danger)' : 'var(--ink)' }};">
                    {{ number_format($customer->outstanding_balance, 2) }}
                </div>
            </div>
        </div>

        {{-- Sales history --}}
        <h2 class="ledger-font text-lg mb-3">Sales History</h2>
        <div class="rounded-lg overflow-hidden mb-6" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Invoice</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Total</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Balance</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customer->sales as $sale)
                        @php
                            // A fully free invoice shows "Free" instead of its payment status
                            $isFreeSale = $sale->is_free;

                            $paymentBadge = $isFreeSale
                                ? 'background:var(--accent-soft); color:var(--accent-ink);'
                                : match(strtolower($sale->payment_status)) {
                                    'paid' => 'background:var(--success-soft); color:var(--success);',
                                    'partial' => 'background:var(--warn-soft); color:var(--warn);',
                                    default => 'background:var(--danger-soft); color:var(--danger);',
                                };
                        @endphp
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3">
                                <a href="{{ route('sales.show', $sale) }}" class="font-medium" style="color:var(--accent-ink);">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $sale->sold_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-right num">{{ number_format($sale->total, 2) }}</td>
                            <td class="px-4 py-3 text-right num" style="color:{{ $sale->balance > 0 ? 'var(--danger)' : 'var(--ink-soft)' }};">
                                {{ number_format($sale->balance, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge" style="{{ $paymentBadge }}">{{ $isFreeSale ? 'Free' : ucfirst($sale->payment_status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-sm" style="color:var(--ink-soft);" colspan="5">No sales yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.pixel>