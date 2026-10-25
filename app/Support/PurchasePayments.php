<?php
// Place in app/Support/PurchasePayments.php  (new file)

namespace App\Support;

use App\Models\Purchase;

class PurchasePayments
{
    /**
     * Pay up to $amount (BASE currency) toward one purchase.
     * Never pays more than what is due. Returns the amount actually applied.
     * Call inside a DB::transaction().
     */
    public static function apply(Purchase $purchase, string $amount, $accountId, ?string $date = null, ?string $note = null): string
    {
        // Re-read the row locked, so a double-click can't overpay.
        $fresh = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

        if ($fresh->isCancelled()) {
            return '0.00';
        }

        $due = $fresh->amount_due_base;
        $applied = bccomp($amount, $due, 2) > 0 ? $due : bcadd($amount, '0', 2);

        if (bccomp($applied, '0', 2) <= 0) {
            return '0.00';
        }

        $fresh->update([
            'amount_paid' => bcadd((string) $fresh->amount_paid, $applied, 2),
        ]);

        // Money leaves the chosen account.
        $description = 'Payment on purchase #'.$fresh->id;
        if ($fresh->supplier) {
            $description .= ' — '.$fresh->supplier->name;
        }
        if ($note) {
            $description .= ' ('.$note.')';
        }

        $fresh->transactions()->create([
            'account_id' => $accountId,
            'transaction_date' => $date ? $date.' '.now()->format('H:i:s') : now(),
            'type' => 'expense',
            'amount' => $applied,
            'description' => $description,
            'created_by' => auth()->id(),
        ]);

        return $applied;
    }
}