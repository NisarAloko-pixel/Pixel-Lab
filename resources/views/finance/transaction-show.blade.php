@php
    use Illuminate\Support\Facades\Route;
@endphp
@php
    $ref = $transaction->reference_type ? class_basename($transaction->reference_type) : null;
    $credit = $transaction->isCredit();
    $typeLabel = match (true) {
        $ref === 'Payment'  => 'Sale payment',
        $ref === 'Purchase' => $credit ? 'Purchase refund' : 'Purchase',
        $ref === 'Advance'  => $credit ? 'Repayment' : 'Advance',
        $ref === 'Expense'  => 'Expense',
        $ref !== null       => $credit ? 'Income' : ucfirst(str_replace('_', ' ', $ref)),
        default             => ucfirst(str_replace('_', ' ', $transaction->type)),
    };
@endphp
@php
    // Where to go to view the linked record (invoice, purchase, advance...)
    $linkUrl = null;
    $linkText = null;
    $r = $transaction->reference;

    if ($r) {
        switch ($ref) {
            case 'Payment': // payment -> the record it pays (payable), e.g. the sale invoice
                $payable = $r->payable;
                $payableType = $payable ? class_basename($payable) : null;

                if ($payableType === 'Sale' && Route::has('sales.show')) {
                    $linkUrl = route('sales.show', $payable->id);
                    $linkText = 'View invoice';
                } elseif ($payableType === 'Purchase' && Route::has('purchases.show')) {
                    $linkUrl = route('purchases.show', $payable->id);
                    $linkText = 'View purchase';
                }
                break;
            case 'Purchase':
                if (Route::has('purchases.show')) {
                    $linkUrl = route('purchases.show', $r->id);
                    $linkText = 'View purchase';
                }
                break;
            case 'Advance': // advance -> the employee it belongs to (party is polymorphic)
                $partyType = $r->party_type ? class_basename($r->party_type) : null;

                if ($partyType === 'Employee' && Route::has('finance.employee.transactions')) {
                    $linkUrl = route('finance.employee.transactions', $r->party_id);
                    $linkText = 'View employee transactions';
                } elseif (Route::has('finance.advance')) {
                    $linkUrl = route('finance.advance');
                    $linkText = 'View advances';
                }
                break;
            case 'Expense':
                if (Route::has('expenses.show')) {
                    $linkUrl = route('expenses.show', $r->id);
                    $linkText = 'View expense';
                }
                break;
        }
    }
@endphp
<x-layouts.pixel>
<div style="background:var(--paper); color:var(--ink);">
    <div class="max-w-2xl mx-auto px-6 py-10">

        <a href="{{ route('finance.transaction') }}" class="text-sm" style="color:var(--accent-ink);">&larr; Back to transactions</a>

        <div class="flex justify-between items-end mt-2 mb-6">
            <div>
                <h1 class="ledger-font text-3xl" style="color:var(--ink);">Transaction #{{ $transaction->id }}</h1>
                <p class="text-sm mt-1" style="color:var(--ink-soft);">{{ $transaction->description }}</p>
            </div>
            <span class="badge" style="background:{{ $credit ? 'var(--success-soft)' : 'var(--danger-soft)' }}; color:{{ $credit ? 'var(--success)' : 'var(--danger)' }};">
                {{ $typeLabel }}
            </span>
        </div>

        <div class="rounded-lg p-6 grid grid-cols-2 gap-y-4 gap-x-6" style="background:var(--surface); border:1px solid var(--line);">
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Amount</div>
                <div class="text-lg font-semibold num" style="color:{{ $credit ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $credit ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                </div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Date</div>
                <div class="text-lg font-semibold num">{{ $transaction->transaction_date->format('Y-m-d') }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Account</div>
                <div class="text-lg font-semibold">{{ $transaction->account->name ?? 'â€”' }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Linked to</div>
                <div class="text-lg font-semibold leading-snug">
                    @if ($transaction->reference)
                        {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                        @if ($linkUrl)
                            {{-- Plain icon, sits right after the text on the same baseline --}}
                            <a href="{{ $linkUrl }}" title="{{ $linkText }}" aria-label="{{ $linkText }}"
                               class="inline-block align-middle ml-1.5 -mt-0.5 opacity-80 hover:opacity-100 transition-opacity"
                               style="color:var(--accent-ink);">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                        @endif
                    @else
                        â€”
                    @endif
                </div>
            </div>
            <div class="col-span-2">
                <div class="text-xs" style="color:var(--ink-soft);">Description</div>
                <div class="text-sm">{{ $transaction->description ?: 'â€”' }}</div>
            </div>
            <div>
                <div class="text-xs" style="color:var(--ink-soft);">Recorded</div>
                <div class="text-sm num">{{ $transaction->created_at?->format('Y-m-d H:i') ?? 'â€”' }}</div>
            </div>
        </div>
    </div>
</div>
</x-layouts.pixel>