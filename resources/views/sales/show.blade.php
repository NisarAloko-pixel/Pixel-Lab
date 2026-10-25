<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-5xl mx-auto px-6 py-10">


        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-1 text-sm font-medium mb-4" style="color:var(--accent-ink);">
            &larr; Back to sales
        </a>

        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="ledger-font text-3xl mb-1">Invoice {{ $sale->invoice_number }}</h1>
                <p class="text-sm" style="color:var(--ink-soft);">
                    {{ $sale->sold_at->format('Y-m-d H:i') }} &middot; {{ $sale->customer->name ?? 'Walk-in' }}
                </p>
                <div class="flex gap-2 mt-2">
                   @php
    $paymentBadge = $sale->is_free
        ? 'background:var(--accent-soft); color:var(--accent-ink);'
        : match(strtolower($sale->payment_status)) {
            'paid' => 'background:var(--success-soft); color:var(--success);',
            'partial' => 'background:var(--warn-soft); color:var(--warn);',
            default => 'background:var(--danger-soft); color:var(--danger);',
        };
@endphp
<span class="badge" style="{{ $paymentBadge }}">{{ $sale->is_free ? 'Free' : ucfirst($sale->payment_status) }}</span>
                   <span class="badge" style="{{ strtolower($sale->status) === 'cancelled' ? 'background:var(--danger-soft); color:var(--danger);' : 'background:var(--surface); color:var(--ink-soft); border:1px solid var(--line);' }}">{{ ucfirst($sale->status) }}</span>
                 </div>
               @if (strtolower($sale->status) === 'cancelled' && $sale->cancellation_reason)
    <div class="rounded-md px-4 py-3 mt-3 text-sm" style="background:var(--warn-soft); color:var(--danger);">
        <span class="font-semibold">Cancelled Reason:</span> {{ $sale->cancellation_reason }}
    </div>
@endif
            </div>

            <div class="flex gap-2">
                 {{-- NEW --}}
    <button type="button" data-invoice-url="{{ route('sales.invoice', $sale) }}"
    class="text-sm font-semibold px-4 py-2.5 rounded-md"
    style="background:var(--ink); color:var(--paper);">
    Print invoice
</button>
                @if ($sale->status !== 'cancelled')
                    <a href="{{ route('sales.edit', $sale) }}"
                        class="text-sm font-semibold px-4 py-2.5 rounded-md"
                        style="background:var(--accent-soft); color:var(--accent-ink);">
                        Edit sale
                    </a>
                    <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="cancel-sale-form">
    @csrf
    <input type="hidden" name="reason">
    <button type="submit" class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--danger); color:#FFF;">
        Cancel sale
    </button>
</form>
                @endif
            </div>
        </div>

        {{-- Items --}}
        @php
            // grams_used on Product is stored as a per-unit figure (this is the same
            // field the sale form itself displays directly per unit), so total grams
            // for a line is per-unit grams × quantity sold.
            $totalGramsUsed = $sale->items->sum(function ($item) {
                return $item->product
                    ? (float) $item->product->grams_used * (float) $item->quantity
                    : 0;
            });

            // Flatten every item's extras into one list for the standalone extras table.
            $allExtras = $sale->items->flatMap(function ($item) {
                return collect($item->extra_items ?? [])->filter(fn ($e) => !empty($e['name']));
            });
        @endphp
        <div class="rounded-lg overflow-hidden mb-6" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Item</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Qty</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Grams / item</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Grams used</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Unit price</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Discount</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        @php
                            $gramsPerItem = $item->product ? (float) $item->product->grams_used : null;
                            $lineGrams = $gramsPerItem !== null ? $gramsPerItem * (float) $item->quantity : null;
                        @endphp
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3">
                                {{ $item->description }}
                                @if ($item->is_free)
                                    <span class="badge" style="margin-left:6px; background:var(--success-soft); color:var(--success); border:1px solid var(--success); text-transform:uppercase; letter-spacing:.04em;">
                                        Sold free
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right num">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">
                                {{ $gramsPerItem !== null ? rtrim(rtrim(number_format($gramsPerItem, 3), '0'), '.') . ' g' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">
                                {{ $lineGrams !== null ? rtrim(rtrim(number_format($lineGrams, 3), '0'), '.') . ' g' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right num">
                                @if ($item->is_free)
                                    <span style="color:var(--success); font-weight:600;">Free</span>
                                @else
                                    {{ number_format($item->unit_price, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right num" style="color:var(--ink-soft);">
                                {{ $item->is_free ? '—' : number_format($item->discount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right num font-medium">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if ($totalGramsUsed > 0)
                    <tfoot>
                        <tr style="background:var(--paper);">
                            <td class="px-4 py-2 text-xs font-semibold" colspan="3">Total grams used</td>
                            <td class="px-4 py-2 text-right num text-xs font-semibold" colspan="4">
                                {{ rtrim(rtrim(number_format($totalGramsUsed, 3), '0'), '.') }} g
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Other items / supplies used --}}
        @if ($allExtras->isNotEmpty())
            <div class="rounded-lg overflow-hidden mb-6" style="background:var(--surface); border:1px solid var(--line);">
                <div class="px-4 py-3" style="background:var(--paper); border-bottom:1px solid var(--line);">
                    <h2 class="ledger-font text-base">Other items / supplies used</h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left" style="border-bottom:1px solid var(--line);">
                            <th class="px-4 py-2.5 font-medium" style="color:var(--ink-soft);">Supply / item</th>
                            <th class="px-4 py-2.5 font-medium text-right" style="color:var(--ink-soft);">Quantity / Lenght</th>
                            <th class="px-4 py-2.5 font-medium text-right" style="color:var(--ink-soft);">Cost / item</th>
                            <th class="px-4 py-2.5 font-medium text-right" style="color:var(--ink-soft);">Total cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allExtras as $extra)
                            @php
                                $extraQty = (float) ($extra['quantity'] ?? 0);
                                $extraCost = (float) ($extra['cost'] ?? 0);
                                $costPerItem = $extraQty > 0 ? $extraCost / $extraQty : null;
                            @endphp
                            <tr style="border-bottom:1px solid var(--line);">
                                <td class="px-4 py-2.5">
                                    {{ $extra['name'] }}
                                    @if (empty($extra['supply_id']))
                                        <span class="badge ml-1" style="background:var(--surface); color:var(--ink-soft); border:1px solid var(--line);">Custom</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right num">
                                    {{ $extraQty > 0 ? rtrim(rtrim(number_format($extraQty, 3), '0'), '.') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right num" style="color:var(--ink-soft);">
                                    {{ $costPerItem !== null ? number_format($costPerItem, 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right num">
                                    {{ $extraCost > 0 ? number_format($extraCost, 2) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--paper);">
                            <td class="px-4 py-2 text-xs font-semibold" colspan="3">Total extras cost</td>
                            <td class="px-4 py-2 text-right num text-xs font-semibold">
                                {{ number_format($allExtras->sum(fn ($e) => (float) ($e['cost'] ?? 0)), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Totals --}}
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                @php
                    $isProfit = (float) $sale->gross_profit >= 0;
                @endphp
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between"><span style="color:var(--ink-soft);">Subtotal</span><span class="num">{{ number_format($sale->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span style="color:var(--ink-soft);">Discount</span><span class="num">-{{ number_format($sale->discount, 2) }}</span></div>
                    <div class="flex justify-between"><span style="color:var(--ink-soft);">Tax</span><span class="num">+{{ number_format($sale->tax, 2) }}</span></div>
                    <div class="flex justify-between pt-2 mt-1 ledger-font text-lg" style="border-top:1px solid var(--line);">
                        <span>Total</span><span class="num">{{ number_format($sale->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2"><span style="color:var(--ink-soft);">Paid</span><span class="num">{{ number_format($sale->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between font-semibold"><span>Balance</span><span class="num" style="color:{{ $sale->balance > 0 ? 'var(--danger)' : 'var(--ink)' }};">{{ number_format($sale->balance, 2) }}</span></div>
                    <div class="flex justify-between items-center mt-2 px-3 py-2 rounded-md font-semibold"
                        style="background:{{ $isProfit ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $isProfit ? 'var(--success)' : 'var(--danger)' }};">
                        <span>Gross profit</span>
                        <span class="num">{{ number_format($sale->gross_profit, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <h2 class="ledger-font text-base mb-3">Payments</h2>

                @if ($sale->balance > 0 && $sale->status !== 'cancelled')
                    <div class="rounded-md px-3 py-2 mb-3 text-sm" style="background:var(--warn-soft); color:var(--warn);">
                        Balance due: <span class="num font-semibold">{{ number_format($sale->balance, 2) }}</span>
                    </div>
                @endif

                @forelse ($sale->payments as $payment)
                    <div class="flex justify-between text-sm py-1.5" style="border-bottom:1px solid var(--line);">
                        <span style="color:var(--ink-soft);">{{ $payment->paid_at->format('Y-m-d') }} &middot; {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                        <span class="num font-medium">{{ number_format($payment->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm" style="color:var(--ink-soft);">No payments recorded.</p>
                @endforelse

                @if ($sale->balance > 0 && $sale->status !== 'cancelled')
                    <form method="POST" action="{{ route('sales.payments.store', $sale) }}" class="mt-4 pt-4" style="border-top:1px dashed var(--line);">
                        @csrf
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Amount</label>
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ number_format($sale->balance, 2, '.', '') }}"
                                    class="w-full rounded-md px-3 py-2 text-sm num focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium mb-1" style="color:var(--ink-soft);">Method</label>
                                <select name="payment_method" class="w-full rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" style="border:1px solid var(--line); background:var(--paper); --tw-ring-color:var(--accent);">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="w-full text-sm font-semibold py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                            Record payment
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@include('sales._invoice-modal')
@include('sales._cancel-modal')
</x-layouts.pixel>