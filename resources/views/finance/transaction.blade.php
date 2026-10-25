@php
    use Illuminate\Support\Facades\Route;

    /**
     * Friendly type label. "Expense" is only used for real expenses;
     * purchases, advances, sale payments etc. get their own label.
     */
    $typeLabel = function ($t) {
        $ref = $t->reference_type ? class_basename($t->reference_type) : null;
        $credit = $t->isCredit();

        return match (true) {
            $ref === 'Payment'  => 'Sale payment',
            $ref === 'Purchase' => $credit ? 'Purchase refund' : 'Purchase',
            $ref === 'Advance'  => $credit ? 'Repayment' : 'Advance',
            $ref === 'Expense'  => 'Expense',
            $ref !== null       => $credit ? 'Income' : ucfirst(str_replace('_', ' ', $ref)),
            default             => ucfirst(str_replace('_', ' ', $t->type)),
        };
    };
@endphp
<x-layouts.pixel>
<style>
    :root { --btn-dark: #21262F; }
    [data-theme="dark"] { --btn-dark: #3A4050; }
</style>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-6xl mx-auto px-6 py-10">

        @if (session('error'))
            <div class="rounded-md px-4 py-3 mb-6 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-line);">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Transactions</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">Every movement of money, in one ledger.</p>
            </div>
            <a href="{{ route('finance.transaction.create') }}"
                class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--accent); color:#FFF;">
                New Transaction
            </a>
        </div>

        <form method="GET" class="flex flex-wrap gap-2 mb-5">
            <input type="date" name="from" value="{{ request('from') }}" class="field" style="color:var(--ink);">
            <input type="date" name="to" value="{{ request('to') }}" class="field" style="color:var(--ink);">
            <select name="account_id" class="field" style="color:var(--ink);">
                <option value="">All accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>{{ $account->name }}</option>
                @endforeach
            </select>
            <select name="type" class="field" style="color:var(--ink);">
                <option value="">All types</option>
                <option value="income" @selected(request('type') == 'income')>Income</option>
                <option value="expense" @selected(request('type') == 'expense')>Expense</option>
                <option value="transfer_in" @selected(request('type') == 'transfer_in')>Transfer In</option>
                <option value="transfer_out" @selected(request('type') == 'transfer_out')>Transfer Out</option>
            </select>
            <button class="text-sm font-semibold px-4 py-2.5 rounded-md" style="background:var(--btn-dark); color:#FFF;">
                Filter
            </button>
        </form>

        <div class="rounded-lg overflow-hidden" style="background:var(--surface); border:1px solid var(--line);">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background:var(--paper); border-bottom:1px solid var(--line);">
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Date</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Type</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Description</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Account</th>
                        <th class="px-4 py-3 font-medium" style="color:var(--ink-soft);">Linked to</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Amount</th>
                        <th class="px-4 py-3 font-medium text-right" style="color:var(--ink-soft);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr style="border-bottom:1px solid var(--line);">
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="badge" style="background:{{ $transaction->isCredit() ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $transaction->isCredit() ? 'var(--success)' : 'var(--danger)' }};">
                                    {{ $typeLabel($transaction) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $transaction->description }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">{{ $transaction->account->name }}</td>
                            <td class="px-4 py-3" style="color:var(--ink-soft);">
                                @if ($transaction->reference)
                                    {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right num font-medium" style="color:{{ $transaction->isCredit() ? 'var(--success)' : 'var(--danger)' }};">
                                {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-1">
                                    <a href="{{ route('finance.transaction.show', $transaction) }}" class="icon-btn" style="color:var(--ink-soft);" title="View details">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    @if (!$transaction->reference_type)
                                        <form method="POST" action="{{ route('finance.transaction.destroy', $transaction) }}"
                                            onsubmit="return confirm('Delete this transaction? This cannot be undone.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="icon-btn" style="color:var(--danger);" title="Delete">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm" style="color:var(--ink-soft);">
                                No transactions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $transactions->links() }}</div>
    </div>
</div>
</x-layouts.pixel>