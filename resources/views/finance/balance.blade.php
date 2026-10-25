<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Balance</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Where the money actually sits, right now.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('depositModal').classList.remove('hidden')"
                    class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    Add balance
                </button>
                <button onclick="document.getElementById('transferModal').classList.remove('hidden')"
                    class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    Transfer between accounts
                </button>
            </div>
        </div>
{{-- Totals, one card per currency --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:24px;">
    @forelse ($totalsByCurrency as $code => $total)
        @php
            $currencyAccounts = $accounts->filter(fn ($a) => ($a->currency ?: $baseCurrency) === $code);
        @endphp
        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <p class="text-sm" style="color:var(--ink-soft);">Total balance &middot; {{ $code }}</p>
            <p class="ledger-font text-4xl mt-1 num"
                style="{{ bccomp((string) $total, '0', 2) < 0 ? 'color:var(--danger);' : '' }}">
                {{ number_format($total, 2) }}
                <span class="text-lg" style="color:var(--ink-soft); margin-left:6px;">{{ $code }}</span>
            </p>
           <p class="text-xs mt-2" style="color:var(--ink-soft);">
    {{ $currencyAccounts->pluck('name')->join(', ') }}
</p>
        </div>
    @empty
        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line); color:var(--ink-soft);">
            No accounts yet. Add one below.
        </div>
    @endforelse
</div>

        {{-- Accounts --}}
        <div class="flex justify-between items-center mb-3">
            <p class="text-sm font-medium" style="color:var(--ink);">Accounts</p>
            @can('balance.create')
                <button type="button" onclick="document.getElementById('accountAddModal').classList.remove('hidden')"
                    class="text-sm font-semibold px-3 py-1.5 rounded-md" style="background:var(--accent); color:#FFF;">
                    + Add account
                </button>
            @endcan
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            @foreach ($accounts as $account)
                <div class="rounded-lg p-5" style="background:var(--surface); border:1px solid var(--line);">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium" style="color:var(--ink);">{{ $account->name }}</span>
                        <span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">
                            {{ ucfirst($account->type) }}@if ($account->currency) &middot; {{ $account->currency }}@endif
                        </span>
                    </div>
                    <div class="flex items-end justify-between">
                        <p class="ledger-font text-2xl num"
                            style="{{ (float) $account->current_balance < 0 ? 'color:var(--danger);' : '' }}">
                            {{ number_format($account->current_balance, 2) }}
                            <span class="text-sm" style="color:var(--ink-soft);">{{ $account->currency ?: $baseCurrency }}</span>
                        </p>
                        <div class="flex items-center gap-1">
                            @can('balance.create')
                                {{-- Edit balance: direct correction, no transaction recorded --}}
                                <button type="button" class="icon-btn" style="color:var(--accent-ink);" title="Edit balance"
                                    onclick="openAdjust({{ $account->id }}, @js($account->name), {{ (float) $account->current_balance }}, @js($account->currency ?: $baseCurrency))">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                    </svg>
                                </button>
                                {{-- Edit account name / currency --}}
                                <button type="button" class="icon-btn" style="color:var(--ink-soft);" title="Edit account"
                                    onclick="openAccountEdit(@js($account->name), @js($account->currency), @js(route('finance.balance.accounts.update', $account)))">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h0a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>
                                    </svg>
                                </button>
                            @endcan
                            @can('balance.delete')
                                <form method="POST" action="{{ route('finance.balance.accounts.destroy', $account) }}"
                                    data-confirm="Delete {{ $account->name }}?"
                                    data-confirm-text="Only accounts with a 0.00 balance can be removed."
                                    data-confirm-button="Delete">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Delete account">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Last 30 days, grouped by day --}}
        @php
            $days = $transactions->groupBy(fn ($t) => $t->transaction_date->format('Y-m-d'));
            $transferGroups = $transactions->whereIn('type', ['transfer_in', 'transfer_out'])->groupBy('transfer_group_id');
        @endphp

        <div class="rounded-lg p-6" style="background:var(--surface); border:1px solid var(--line);">
            <p class="text-sm font-medium mb-4" style="color:var(--ink);">Last 30 days</p>

            <div style="overflow-x:auto;">
                <table class="w-full text-sm" style="min-width:720px;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--line);">
                            <th class="text-left py-2" style="color:var(--ink-soft);">Account</th>
                            <th class="text-left py-2" style="color:var(--ink-soft);">Source</th>
                            <th class="text-left py-2" style="color:var(--ink-soft);">Description</th>
                            <th class="text-right py-2" style="color:var(--ink-soft);">In</th>
                            <th class="text-right py-2" style="color:var(--ink-soft);">Out</th>
                            <th class="text-right py-2" style="color:var(--ink-soft);">Net / Edit</th>
                        </tr>
                    </thead>

                    @forelse ($days as $date => $items)
                        @php
                            // Daily totals exclude transfers between your own accounts
                            $real = $items->reject(fn ($x) => in_array($x->type, ['transfer_in', 'transfer_out']));
                            $in   = $real->filter(fn ($x) => $x->isCredit())->sum('amount');
                            $out  = $real->reject(fn ($x) => $x->isCredit())->sum('amount');
                            $net  = $in - $out;
                        @endphp

                        <tbody>
                            {{-- Day header with totals --}}
                            <tr style="background:var(--paper); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
                                <td colspan="3" class="py-2 px-1 font-semibold" style="color:var(--ink);">
                                    {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                                </td>
                                <td class="py-2 text-right num font-semibold" style="color:var(--success);">{{ number_format($in, 2) }}</td>
                                <td class="py-2 text-right num font-semibold" style="color:var(--danger);">{{ number_format($out, 2) }}</td>
                                <td class="py-2 text-right num font-semibold" style="color:{{ $net < 0 ? 'var(--danger)' : 'var(--ink)' }};">{{ number_format($net, 2) }}</td>
                            </tr>

                            {{-- Entries for that day --}}
                            @foreach ($items as $t)
                                @php
                                    $isTransfer = in_array($t->type, ['transfer_in', 'transfer_out']);
                                    $source = match (class_basename((string) $t->reference_type)) {
                                        'Payment'  => 'Sale payment',
                                        'Expense'  => 'Expense',
                                        'Purchase' => 'Purchase',
                                        default    => $isTransfer ? 'Transfer' : 'Manual',
                                    };

                                    $from = $to = $note = null;
                                    if ($isTransfer) {
                                        $pair = $transferGroups->get($t->transfer_group_id, collect());
                                        $from = $pair->firstWhere('type', 'transfer_out')?->account?->name ?? '?';
                                        $to   = $pair->firstWhere('type', 'transfer_in')?->account?->name ?? '?';
                                        $note = in_array($t->description, ['Transfer to account', 'Transfer from account']) ? null : $t->description;
                                    }
                                @endphp

                                <tr style="border-bottom:1px solid var(--line); {{ $isTransfer ? 'opacity:0.6;' : '' }}">
                                    <td class="py-2 px-1">{{ $t->account->name ?? '—' }}</td>
                                    <td class="py-2"><span class="badge" style="background:var(--accent-soft); color:var(--accent-ink);">{{ $source }}</span></td>
                                    <td class="py-2" style="color:var(--ink-soft);">
                                        @if ($isTransfer)
                                            {{ $from }} &rarr; {{ $to }}@if ($note) <span>&middot; {{ $note }}</span>@endif
                                        @else
                                            {{ $t->description }}
                                        @endif
                                    </td>
                                    <td class="py-2 text-right num" style="color:var(--success);">{{ $t->isCredit() ? number_format($t->amount, 2) : '' }}</td>
                                    <td class="py-2 text-right num" style="color:var(--danger);">{{ $t->isCredit() ? '' : number_format($t->amount, 2) }}</td>
                                    <td class="py-2 text-right">
                                        @if ($source === 'Manual' && ! $isTransfer)
                                            @can('balance.create')
                                                <button type="button" class="icon-btn" style="color:var(--accent-ink);" title="Edit entry"
                                                    onclick="openEdit({{ $t->id }}, {{ $t->account_id }}, {{ (float) $t->amount }}, @js($t->transaction_date->format('Y-m-d')), @js($t->description), @js(route('finance.balance.transactions.update', $t)))">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 20h9"/>
                                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                    </svg>
                                                </button>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @empty
                        <tbody>
                            <tr><td colspan="6" class="py-8 text-center" style="color:var(--ink-soft);">No activity yet.</td></tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            <p class="text-xs mt-4" style="color:var(--ink-soft);">Transfers between your accounts are shown faded and aren't counted in the daily totals.</p>
        </div>
    </div>
</div>

{{-- Add balance modal (recorded) --}}
<div id="depositModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Add balance</h2>
        <form method="POST" action="{{ route('finance.balance.deposit') }}" class="space-y-3">
            @csrf
            <select name="account_id" required class="field w-full">
                <option value="">Account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency ?: $baseCurrency }})</option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount" required class="field w-full">
            <input type="date" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required class="field w-full">
            <input type="text" name="description" placeholder="Note (optional, e.g. Opening balance)" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('depositModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- Transfer modal --}}
<div id="transferModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Transfer between accounts</h2>
        <form method="POST" action="{{ route('finance.balance.transfer') }}" class="space-y-3">
            @csrf
            <select name="from_account_id" id="transferFrom" required class="field w-full" onchange="updateTransferRate()">
                <option value="">From account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" data-currency="{{ $account->currency ?: $baseCurrency }}">{{ $account->name }} ({{ $account->currency ?: $baseCurrency }})</option>
                @endforeach
            </select>
            <select name="to_account_id" id="transferTo" required class="field w-full" onchange="updateTransferRate()">
                <option value="">To account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" data-currency="{{ $account->currency ?: $baseCurrency }}">{{ $account->name }} ({{ $account->currency ?: $baseCurrency }})</option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="0.01" name="amount" id="transferAmount" placeholder="Amount (in the sending account's currency)" required class="field w-full" oninput="updateTransferRate()">

            <div id="transferRateBox" class="hidden">
                <label id="transferRateLabel" class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Exchange rate</label>
                <input type="number" step="0.000001" min="0.000001" name="exchange_rate" id="transferRate" class="field w-full" oninput="updateTransferRate()">
                <p id="transferPreview" class="text-xs mt-1" style="color:var(--ink-soft);"></p>
            </div>

            <input type="date" name="transaction_date" value="{{ now()->format('Y-m-d') }}" required class="field w-full">
            <input type="text" name="description" placeholder="Note (optional)" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('transferModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Transfer</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit balance modal (pencil): direct correction, nothing recorded --}}
<div id="adjustModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-1" style="color:var(--ink);">Edit balance</h2>
        <p class="text-sm mb-1" style="color:var(--ink-soft);">
            <span id="adjustName" style="color:var(--ink); font-weight:500;"></span> &mdash;
            current <span id="adjustCurrent" class="num"></span>
        </p>
        <p class="text-xs mb-4" style="color:var(--ink-soft);">
            This corrects the balance directly and is not recorded as a transaction. For real money in, use Add balance.
        </p>
        <form method="POST" action="{{ route('finance.balance.adjust') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="account_id" id="adjustAccountId">
            <input type="number" step="0.01" name="new_balance" id="adjustNewBalance" placeholder="New balance (can be negative)" required class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit entry modal --}}
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Edit entry</h2>
        <form method="POST" id="editForm" class="space-y-3">
            @csrf @method('PUT')
            <select name="account_id" id="editAccount" required class="field w-full">
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="0.01" name="amount" id="editAmount" required class="field w-full">
            <input type="date" name="transaction_date" id="editDate" required class="field w-full">
            <input type="text" name="description" id="editDescription" placeholder="Note" class="field w-full">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit account modal --}}
<div id="accountModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Edit account</h2>
        <form method="POST" id="accountForm" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Account name</label>
                <input type="text" name="name" id="accountName" required maxlength="255" class="field w-full">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Currency</label>
                <select name="currency" id="accountCurrency" class="field w-full">
                    <option value="">Not set</option>
                    @foreach ($currencies as $code => $label)
                        <option value="{{ $code }}">{{ $code }}{{ is_string($label) ? ' — '.$label : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('accountModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Add account modal --}}
<div id="accountAddModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.4);">
    <div class="rounded-lg p-6 w-full max-w-md" style="background:var(--surface); border:1px solid var(--line);">
        <h2 class="ledger-font text-xl mb-4" style="color:var(--ink);">Add account</h2>
        <form method="POST" action="{{ route('finance.balance.accounts.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Account name</label>
                <input type="text" name="name" required maxlength="255" placeholder="e.g. Dollar Cash" class="field w-full">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Type</label>
                <select name="type" required class="field w-full">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Currency</label>
                <select name="currency" class="field w-full">
                    @foreach ($currencies as $code => $label)
                        <option value="{{ $code }}" @selected($code === $baseCurrency)>{{ $code }}{{ is_string($label) ? ' — '.$label : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--ink-soft);">Opening balance (optional)</label>
                <input type="number" step="0.01" name="opening_balance" value="0" class="field w-full">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('accountAddModal').classList.add('hidden')"
                    class="text-sm px-4 py-2 rounded-md" style="border:1px solid var(--line); color:var(--ink-soft);">Cancel</button>
                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-md" style="background:var(--accent); color:#FFF;">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdjust(id, name, current, currency) {
        document.getElementById('adjustAccountId').value = id;
        document.getElementById('adjustName').textContent = name;
        document.getElementById('adjustCurrent').textContent = current.toFixed(2) + ' ' + currency;
        document.getElementById('adjustNewBalance').value = current.toFixed(2);
        document.getElementById('adjustModal').classList.remove('hidden');
    }

    function openEdit(id, accountId, amount, date, description, url) {
        document.getElementById('editForm').action = url;
        document.getElementById('editAccount').value = accountId;
        document.getElementById('editAmount').value = amount;
        document.getElementById('editDate').value = date;
        document.getElementById('editDescription').value = description || '';
        document.getElementById('editModal').classList.remove('hidden');
    }

    function openAccountEdit(name, currency, url) {
        document.getElementById('accountForm').action = url;
        document.getElementById('accountName').value = name;
        document.getElementById('accountCurrency').value = currency || '';
        document.getElementById('accountModal').classList.remove('hidden');
    }

    function updateTransferRate() {
        const from = document.getElementById('transferFrom');
        const to = document.getElementById('transferTo');
        const box = document.getElementById('transferRateBox');
        const rate = document.getElementById('transferRate');
        const fc = from.selectedOptions[0]?.dataset.currency;
        const tc = to.selectedOptions[0]?.dataset.currency;
        const cross = from.value && to.value && fc && tc && fc !== tc;

        box.classList.toggle('hidden', !cross);
        rate.required = !!cross;
        if (!cross) { rate.value = ''; return; }

        document.getElementById('transferRateLabel').textContent = `Exchange rate: 1 ${fc} = ? ${tc}`;
        const amount = parseFloat(document.getElementById('transferAmount').value) || 0;
        const r = parseFloat(rate.value) || 0;
        document.getElementById('transferPreview').textContent =
            amount && r ? `${to.selectedOptions[0].text.split(' (')[0]} receives ${(amount * r).toFixed(2)} ${tc}` : '';
    }
</script>
</x-layouts.pixel>