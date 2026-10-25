{{-- resources/views/suppliers/show.blade.php --}}
@php
    $baseCurrency = \App\Models\Setting::get('currency', 'USD');
    $purchases = $supplier->purchases->sortByDesc(fn ($p) => [$p->order_date?->timestamp ?? 0, $p->id]);
    $totalPurchases = $supplier->total_purchases;
    $totalPaid = $supplier->total_paid;
    $balance = $supplier->outstanding_balance;
    $balanceColor = (float) $balance > 0 ? 'var(--danger)' : 'var(--ink)';
    $defaultAccountId = $accounts->firstWhere('type', 'cash')?->id ?? $accounts->first()?->id;

    // Data for the "purchase details" popup, keyed by purchase id.
    $details = $purchases->mapWithKeys(function ($p) use ($baseCurrency) {
        $due = (float) $p->amount_due_base;
        $cancelled = $p->isCancelled();

        return [$p->id => [
            'id' => $p->id,
            'item' => $p->displayName(),
            'kind' => $p->item_type === 'filament' ? 'Filament spool' : 'Supply',
            'status' => ucfirst(str_replace('_', ' ', $p->status)),
            'order_date' => $p->order_date?->format('Y-m-d') ?? '—',
            'arrived_at' => $p->arrived_at?->format('Y-m-d H:i') ?? '—',
            'arrived_location' => $p->arrived_location ?: '—',
            'quantity' => rtrim(rtrim(number_format((float) $p->quantity, 2), '0'), '.')
                .($p->item_type === 'supply' && $p->unit ? ' '.$p->unit : ($p->item_type === 'filament' ? ' g' : '')),
            'currency' => $p->currency,
            'is_foreign' => $p->currency !== $baseCurrency,
            'rate' => rtrim(rtrim(number_format((float) $p->exchange_rate, 6), '0'), '.'),
            'price' => number_format((float) $p->total_price, 2),
            'shipping' => number_format((float) $p->shipping_cost, 2),
            'total_orig' => number_format((float) $p->total_price + (float) $p->shipping_cost, 2),
            'total_base' => number_format((float) $p->total_cost_base, 2),
            'paid' => number_format((float) $p->amount_paid, 2),
            'due' => number_format($due, 2),
            'due_raw' => round($due, 2),
            'can_pay' => ! $cancelled && $due > 0,
            'pay_url' => route('purchases.pay', $p),
            'show_url' => route('purchases.show', $p),
            'notes' => $p->notes ?: '',
            'cancelled' => $cancelled,
            'cancel_reason' => $p->cancellation_reason ?: '',
            'payments' => $p->transactions->sortBy('transaction_date')->map(fn ($t) => [
                'date' => $t->transaction_date?->format('Y-m-d') ?? '—',
                'description' => $t->description,
                'account' => $t->account?->name ?? '—',
                'credit' => $t->isCredit(),
                'amount' => number_format((float) $t->amount, 2),
            ])->values()->all(),
        ]];
    })->all();
@endphp

<x-layouts.pixel>
<script>window.PURCHASE_DETAILS = @json($details);</script>

<div style="background:var(--paper); color:var(--ink);"
     x-data="{
        details: window.PURCHASE_DETAILS,
        base: @js($baseCurrency),
        view: { open: false, d: null },
        pay: { open: false, action: '', title: '', max: 0, amount: '', account_id: @js($defaultAccountId), date: new Date().toISOString().slice(0, 10), notes: '' },
        openView(id) {
            this.view.d = this.details[id];
            this.view.open = true;
        },
        openPay(action, title, max) {
            this.pay.action = action;
            this.pay.title = title;
            this.pay.max = max;
            this.pay.amount = max;
            this.pay.notes = '';
            this.pay.open = true;
        }
     }"
     @keydown.escape.window="pay.open = false; view.open = false">
    <div class="max-w-6xl mx-auto px-6 py-10">

        @if (session('error'))
            <div class="rounded-md px-4 py-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-md px-4 py-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                {{ $errors->first() }}
            </div>
        @endif

        <a href="{{ route('suppliers.index') }}" class="text-sm font-medium" style="color:var(--accent-ink);">&larr; Back to suppliers</a>

        <div class="flex justify-between items-end mt-3 mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">{{ $supplier->name }}</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">
                    {{ $supplier->contact_person ?: 'No contact person' }}
                    @if ($supplier->phone) &middot; {{ $supplier->phone }} @endif
                    @if ($supplier->email) &middot; {{ $supplier->email }} @endif
                </p>
                @if ($supplier->address)
                    <p class="text-sm" style="color:var(--ink-soft);">{{ $supplier->address }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                @if ((float) $balance > 0)
                    <button type="button"
                        @click="openPay(@js(route('suppliers.pay', $supplier)), @js('Pay '.$supplier->name), @js((float) $balance))"
                        class="text-sm font-semibold px-4 py-2.5 rounded-md"
                        style="background:var(--success); color:#FFF;">
                        Pay Supplier
                    </button>
                @endif
                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-sm font-semibold px-4 py-2.5 rounded-md"
                   style="border:1px solid var(--line); color:var(--accent-ink);">Edit</a>
                <a href="{{ route('purchases.create') }}" class="text-sm font-semibold px-4 py-2.5 rounded-md"
                   style="background:var(--accent); color:#FFF;">+ New Purchase</a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs font-medium" style="color:var(--ink-soft);">Total Purchases</p>
                <p class="num text-2xl font-medium mt-1">
                    {{ number_format($totalPurchases, 2) }}
                    <span class="text-xs" style="color:var(--ink-soft);">{{ $baseCurrency }}</span>
                </p>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs font-medium" style="color:var(--ink-soft);">Total Paid</p>
                <p class="num text-2xl font-medium mt-1" style="color:var(--success);">
                    {{ number_format($totalPaid, 2) }}
                    <span class="text-xs" style="color:var(--ink-soft);">{{ $baseCurrency }}</span>
                </p>
            </div>
            <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs font-medium" style="color:var(--ink-soft);">Outstanding Balance</p>
                <p class="num text-2xl font-medium mt-1" style="color:{{ $balanceColor }};">
                    {{ number_format($balance, 2) }}
                    <span class="text-xs" style="color:var(--ink-soft);">{{ $baseCurrency }}</span>
                </p>
            </div>
        </div>

        {{-- Purchase history --}}
        <div class="flex justify-between items-baseline mb-3">
            <h2 class="ledger-font text-xl" style="color:var(--ink);">Purchase History</h2>
            <span class="text-xs" style="color:var(--ink-soft);">All amounts in {{ $baseCurrency }}</span>
        </div>

        <div class="rounded-lg overflow-x-auto" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium whitespace-nowrap" style="color:var(--ink-soft);">PO #</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Item</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap" style="color:var(--ink-soft);">Quantity</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap" style="color:var(--ink-soft);">Total</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap" style="color:var(--ink-soft);">Paid</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap" style="color:var(--ink-soft);">Due</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap" style="color:var(--ink-soft);">Payment</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap" style="color:var(--ink-soft);">Status</th>
                        <th class="px-4 py-3 font-medium text-right whitespace-nowrap" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        @php
                            $cancelled = $purchase->isCancelled();
                            $rowTotal = (float) $purchase->total_price + (float) $purchase->shipping_cost;
                            $totalBase = (float) $purchase->total_cost_base;
                            $paid = (float) $purchase->amount_paid;
                            $due = (float) $purchase->amount_due_base;

                            $statusStyle = match ($purchase->status) {
                                'arrived'   => 'background:var(--success-soft); color:var(--success);',
                                'cancelled' => 'background:var(--danger-soft); color:var(--danger);',
                                default     => 'background:var(--warn-soft); color:var(--warn);',
                            };

                            if ($cancelled) {
                                $payLabel = '—';
                                $payStyle = '';
                            } elseif ($due <= 0) {
                                $payLabel = 'Paid';
                                $payStyle = 'background:var(--success-soft); color:var(--success);';
                            } elseif ($paid > 0) {
                                $payLabel = 'Partial';
                                $payStyle = 'background:var(--warn-soft); color:var(--warn);';
                            } else {
                                $payLabel = 'Unpaid';
                                $payStyle = 'background:var(--danger-soft); color:var(--danger);';
                            }
                        @endphp
                        <tr style="border-bottom:1px solid var(--line); {{ $cancelled ? 'opacity:.55;' : '' }}">
                            <td class="px-4 py-3 font-medium whitespace-nowrap">
                                <a href="{{ route('purchases.show', $purchase) }}" class="hover:underline" style="color:var(--accent-ink);">#{{ $purchase->id }}</a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap" style="color:var(--ink-soft);">
                                {{ $purchase->order_date?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-4 py-3" style="{{ $cancelled ? 'text-decoration:line-through;' : '' }}">
                                {{ $purchase->displayName() }}
                            </td>
                            <td class="px-4 py-3 text-right num whitespace-nowrap">
                                {{ rtrim(rtrim(number_format((float) $purchase->quantity, 2), '0'), '.') }}
                                {{ $purchase->item_type === 'supply' ? $purchase->unit : '' }}
                            </td>
                            <td class="px-4 py-3 text-right num whitespace-nowrap">
                                <div class="font-medium">{{ number_format($totalBase, 2) }}</div>
                                @if ($purchase->currency !== $baseCurrency)
                                    <div class="text-xs" style="color:var(--ink-soft);">{{ number_format($rowTotal, 2) }} {{ $purchase->currency }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right num whitespace-nowrap">
                                {{ number_format($paid, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right num whitespace-nowrap {{ $due > 0 ? 'font-medium' : '' }}"
                                style="color:{{ $due > 0 ? 'var(--danger)' : 'var(--ink-soft)' }};">
                                {{ number_format($due, 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($payStyle)
                                    <span class="badge" style="{{ $payStyle }}">{{ $payLabel }}</span>
                                @else
                                    <span style="color:var(--ink-soft);">{{ $payLabel }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge" style="{{ $statusStyle }}">
                                    {{ ucfirst(str_replace('_', ' ', $purchase->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    {{-- View details popup --}}
                                    <button type="button" @click="openView({{ $purchase->id }})" title="View purchase details" aria-label="View purchase details"
                                        class="opacity-80 hover:opacity-100 transition-opacity" style="color:var(--accent-ink);">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>

                                    @if (! $cancelled && $due > 0)
                                        <button type="button"
                                            @click="openPay(@js(route('purchases.pay', $purchase)), @js('Pay purchase #'.$purchase->id), @js(round($due, 2)))"
                                            class="text-xs font-semibold px-3 py-1.5 rounded-md"
                                            style="background:var(--accent); color:#FFF;">
                                            Pay {{ number_format($due, 2) }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No purchases from this supplier yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($purchases->isNotEmpty())
                    <tfoot>
                        <tr style="background:var(--paper); border-top:1px solid var(--line);">
                            <td colspan="4" class="px-4 py-3 text-sm font-semibold" style="color:var(--ink);">Totals</td>
                            <td class="px-4 py-3 text-right num font-semibold whitespace-nowrap">{{ number_format($totalPurchases, 2) }}</td>
                            <td class="px-4 py-3 text-right num font-semibold whitespace-nowrap" style="color:var(--success);">{{ number_format($totalPaid, 2) }}</td>
                            <td class="px-4 py-3 text-right num font-semibold whitespace-nowrap" style="color:{{ $balanceColor }};">{{ number_format($balance, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <p class="text-xs mt-3" style="color:var(--ink-soft);">
            Cancelled purchases are shown faded and are not counted in the totals.
        </p>

    </div>

    {{-- Purchase details popup --}}
    <div x-show="view.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4);">
        <div class="rounded-lg p-6 w-full max-w-lg overflow-y-auto" style="background:var(--surface); border:1px solid var(--line); max-height:90vh;" @click.outside="view.open = false">
            <template x-if="view.d">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="ledger-font text-xl" style="color:var(--ink);" x-text="'Purchase #' + view.d.id"></h2>
                            <p class="text-sm" style="color:var(--ink-soft);" x-text="view.d.item + ' · ' + view.d.kind"></p>
                        </div>
                        <span class="badge" style="background:var(--paper); color:var(--ink-soft); border:1px solid var(--line);" x-text="view.d.status"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm mb-4">
                        <div>
                            <div class="text-xs" style="color:var(--ink-soft);">Order date</div>
                            <div class="num" x-text="view.d.order_date"></div>
                        </div>
                        <div>
                            <div class="text-xs" style="color:var(--ink-soft);">Quantity</div>
                            <div class="num" x-text="view.d.quantity"></div>
                        </div>
                        <div>
                            <div class="text-xs" style="color:var(--ink-soft);">Arrived</div>
                            <div class="num" x-text="view.d.arrived_at"></div>
                        </div>
                        <div>
                            <div class="text-xs" style="color:var(--ink-soft);">Arrived at</div>
                            <div x-text="view.d.arrived_location"></div>
                        </div>
                    </div>

                    <div class="rounded-md p-3 text-sm mb-4" style="background:var(--paper); border:1px solid var(--line); color:var(--ink-soft);">
                        <template x-if="view.d.is_foreign">
                            <div>
                                <div class="flex justify-between">
                                    <span>Price</span>
                                    <strong class="num" style="color:var(--ink);" x-text="view.d.price + ' ' + view.d.currency"></strong>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span>Shipping</span>
                                    <strong class="num" style="color:var(--ink);" x-text="view.d.shipping + ' ' + view.d.currency"></strong>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span>Exchange rate</span>
                                    <strong class="num" style="color:var(--ink);" x-text="'1 ' + view.d.currency + ' = ' + view.d.rate + ' ' + base"></strong>
                                </div>
                            </div>
                        </template>
                        <template x-if="!view.d.is_foreign">
                            <div>
                                <div class="flex justify-between">
                                    <span>Price</span>
                                    <strong class="num" style="color:var(--ink);" x-text="view.d.price + ' ' + base"></strong>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span>Shipping</span>
                                    <strong class="num" style="color:var(--ink);" x-text="view.d.shipping + ' ' + base"></strong>
                                </div>
                            </div>
                        </template>
                        <div class="flex justify-between mt-1 pt-1" style="border-top:1px solid var(--line);">
                            <span class="font-semibold" style="color:var(--ink);">Total</span>
                            <strong class="num" style="color:var(--ink);" x-text="view.d.total_base + ' ' + base"></strong>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span>Paid</span>
                            <strong class="num" style="color:var(--success);" x-text="view.d.paid + ' ' + base"></strong>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span>Due</span>
                            <strong class="num" :style="view.d.can_pay ? 'color:var(--danger)' : 'color:var(--ink-soft)'" x-text="view.d.due + ' ' + base"></strong>
                        </div>
                    </div>

                    <template x-if="view.d.cancelled && view.d.cancel_reason">
                        <p class="text-sm mb-4" style="color:var(--danger);" x-text="'Cancelled: ' + view.d.cancel_reason"></p>
                    </template>
                    <template x-if="view.d.notes">
                        <p class="text-sm mb-4" style="color:var(--ink-soft);" x-text="'Note: ' + view.d.notes"></p>
                    </template>

                    <div class="mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--ink-soft);">Payments</div>
                        <template x-if="view.d.payments.length === 0">
                            <p class="text-sm" style="color:var(--ink-soft);">No payments recorded.</p>
                        </template>
                        <template x-for="(t, i) in view.d.payments" :key="i">
                            <div class="flex justify-between gap-4 text-sm py-1.5" style="border-bottom:1px solid var(--line);">
                                <div>
                                    <div x-text="t.description"></div>
                                    <div class="text-xs" style="color:var(--ink-soft);" x-text="t.date + ' · ' + t.account"></div>
                                </div>
                                <div class="num font-medium whitespace-nowrap" :style="t.credit ? 'color:var(--success)' : 'color:var(--danger)'" x-text="(t.credit ? '+' : '-') + t.amount"></div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <a :href="view.d.show_url" class="text-sm font-medium hover:underline" style="color:var(--accent-ink);">Open full page &rarr;</a>
                        <div class="flex gap-2">
                            <button type="button" @click="view.open = false" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Close</button>
                            <button type="button" x-show="view.d.can_pay"
                                @click="view.open = false; openPay(view.d.pay_url, 'Pay purchase #' + view.d.id, view.d.due_raw)"
                                class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">
                                Pay <span x-text="view.d.due"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Payment modal (used by both "Pay" on a row and "Pay Supplier") --}}
    <div x-show="pay.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
        <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);" @click.outside="pay.open = false">
            <h2 class="ledger-font text-xl mb-1" style="color:var(--ink);" x-text="pay.title"></h2>
            <p class="text-sm mb-4" style="color:var(--ink-soft);">
                Amount due:
                <strong style="color:var(--danger);" x-text="Number(pay.max).toFixed(2) + ' {{ $baseCurrency }}'"></strong>
            </p>

            <form method="POST" :action="pay.action" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Amount ({{ $baseCurrency }})</label>
                    <div class="flex gap-2">
                        <input type="number" step="0.01" min="0.01" :max="pay.max" name="amount" x-model="pay.amount" required class="field w-full">
                        <button type="button" @click="pay.amount = pay.max" class="text-xs font-semibold px-3 rounded-md whitespace-nowrap"
                            style="border:1px solid var(--line); color:var(--accent-ink);">Pay full</button>
                    </div>
                    <p class="text-[11px] mt-1" style="color:var(--ink-soft);">Enter less to make a partial payment. You can't pay more than is due.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Paid from</label>
                    <select name="account_id" x-model="pay.account_id" required class="field w-full">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Payment date</label>
                    <input type="date" name="payment_date" x-model="pay.date" class="field w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Note (optional)</label>
                    <input type="text" name="notes" x-model="pay.notes" maxlength="255" class="field w-full">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="pay.open = false" class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                    <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.pixel>