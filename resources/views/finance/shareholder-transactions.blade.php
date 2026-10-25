<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">


        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('finance.advance', ['tab' => 'shareholders']) }}" class="text-sm inline-flex items-center gap-1 mb-2" style="color:var(--ink-soft);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Shareholders
                </a>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">{{ $shareholder->name }}</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">{{ number_format($shareholder->share_percentage, 2) }}% share · Investment &amp; dividend history</p>
            </div>
            <button onclick="document.getElementById('txnModal').classList.remove('hidden')"
                class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                Record Investment / Dividend
            </button>
        </div>

        <div class="mb-4">
            <span class="badge" style="background:var(--paper); color:var(--ink-soft);">{{ $shareholder->role ?? 'No role set' }}</span>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="rounded-lg p-4" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs" style="color:var(--ink-soft);">Total Invested</p>
                <p class="num text-xl font-semibold mt-1" style="color:var(--success);">{{ number_format($shareholder->total_invested, 2) }} {{ $baseCurrency }}</p>
            </div>
            <div class="rounded-lg p-4" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs" style="color:var(--ink-soft);">Total Dividends Paid</p>
                <p class="num text-xl font-semibold mt-1" style="color:var(--danger);">{{ number_format($shareholder->total_dividends_paid, 2) }} {{ $baseCurrency }}</p>
            </div>
            <div class="rounded-lg p-4" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs" style="color:var(--ink-soft);">Business Total Profit</p>
                <p class="num text-xl font-semibold mt-1">{{ number_format($totalProfit, 2) }} {{ $baseCurrency }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="rounded-lg p-4" style="background:var(--surface); border:1px solid var(--line);">
                <p class="text-xs" style="color:var(--ink-soft);">Their Profit Share ({{ number_format($shareholder->share_percentage, 2) }}%)</p>
                <p class="num text-2xl font-semibold mt-1" style="color:var(--success);">{{ number_format($profitShare, 2) }} {{ $baseCurrency }}</p>
            </div>
            <div class="rounded-lg p-4" style="background:var(--success-soft); border:1px solid var(--success-line);">
                <p class="text-xs" style="color:var(--ink-soft);">Profit Share Available (not yet paid out)</p>
                <p class="num text-2xl font-semibold mt-1" style="color:var(--success);">{{ number_format($availableProfitShare, 2) }} {{ $baseCurrency }}</p>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden mb-6" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Type</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Account</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Note</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Amount</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3 num">{{ \Illuminate\Support\Carbon::parse($tx->transaction_date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="badge" style="background:{{ $tx->type === 'dividend' ? 'var(--danger-soft)' : 'var(--success-soft)' }}; color:{{ $tx->type === 'dividend' ? 'var(--danger)' : 'var(--success)' }};">
                                    {{ $tx->type === 'dividend' ? 'Dividend Paid' : 'Investment' }}
                                </span>
                            </td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $tx->account->name ?? '—' }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $tx->notes ?? '—' }}</td>
                            <td class="px-4 py-3 text-right num font-medium" style="color:{{ $tx->type === 'dividend' ? 'var(--danger)' : 'var(--success)' }};">
                                {{ $tx->type === 'dividend' ? '-' : '+' }}{{ number_format($tx->amount, 2) }} {{ $baseCurrency }}
                                @if ($tx->currency !== $baseCurrency)
                                    <div class="text-xs" style="color:var(--ink-soft); font-weight:normal;">
                                        ({{ number_format($tx->original_amount, 2) }} {{ $tx->currency }} @ {{ number_format($tx->exchange_rate, 4) }})
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('finance.shareholders.transactions.destroy', $tx) }}"
                                    onsubmit="return confirm('Delete this entry? This also removes its linked transaction.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete" class="icon-btn" style="color:var(--danger);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">No transactions recorded for this shareholder yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Record Transaction modal --}}
<div id="txnModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Record Investment / Dividend</h2>
        <form method="POST" action="{{ route('finance.shareholders.transactions.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="shareholder_id" value="{{ $shareholder->id }}">
            <select name="type" required class="field w-full">
                <option value="investment">Investment (money in)</option>
                <option value="dividend">Dividend paid (money out)</option>
            </select>

            <div class="grid grid-cols-2 gap-2">
                <input type="number" step="0.01" name="amount" placeholder="Amount" required class="field w-full">
                <select name="currency" id="txnCurrency" required class="field w-full" onchange="toggleTxnRate()">
                    @foreach ($supportedCurrencies as $code)
                        <option value="{{ $code }}" {{ $code === $baseCurrency ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>
            </div>
            <div id="txnRateWrap" style="display:none;">
                <input type="number" step="0.000001" name="exchange_rate" id="txnRateInput" placeholder="Exchange rate to {{ $baseCurrency }}" class="field w-full">
            </div>

            <select name="account_id" required class="field w-full">
                <option value="">Account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            <input type="date" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required class="field w-full">
            <input type="text" name="notes" placeholder="Note (optional)" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('txnModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    const BASE_CURRENCY = @json($baseCurrency);

    function toggleTxnRate() {
        const currency = document.getElementById('txnCurrency').value;
        const wrap = document.getElementById('txnRateWrap');
        const rateInput = document.getElementById('txnRateInput');
        const isBase = currency === BASE_CURRENCY;
        wrap.style.display = isBase ? 'none' : 'block';
        rateInput.required = !isBase;
        if (isBase) rateInput.value = 1;
    }
    document.addEventListener('DOMContentLoaded', toggleTxnRate);
</script>
</x-layouts.pixel>