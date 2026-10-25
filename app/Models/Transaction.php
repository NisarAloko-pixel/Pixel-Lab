<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Auditable;
use App\Models\Payment;
use App\Models\Sale;

class Transaction extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        "account_id", "transaction_date", "type", "amount", "description",
        "reference_type", "reference_id", "transfer_group_id", "created_by",
    ];

    protected $casts = [
        "transaction_date" => "date",
        "amount" => "decimal:2",
    ];
    public static function recordSalePayment(Payment $payment, Sale $sale): self
{
    // Assumes your cash account has type = 'physical'. Check the value in your accounts table.
    $type = match ($payment->payment_method) {
    'bank'  => 'bank',
    default => 'cash',
};

$account = Account::where('type', $type)->where('is_active', true)->orderBy('id')->first();

    if (! $account) {
        throw new \RuntimeException('No active physical account to receive sale payments.');
    }

    return $payment->transactions()->create([
        'account_id'       => $account->id,
        'transaction_date' => $payment->paid_at ?? now(),
        'type'             => 'income',
        'amount'           => $payment->amount,
        'description'      => "Payment for invoice {$sale->invoice_number}",
        'created_by'       => auth()->id(),
    ]);
}

/** Puts the money back out of the account with an offsetting entry, so history is kept. */
public static function reverseSalePayments(Sale $sale): void
{
    foreach ($sale->payments as $payment) {
        if ($payment->transactions()->where('type', 'expense')->exists()) {
            continue; // already reversed
        }

        foreach ($payment->transactions()->where('type', 'income')->get() as $original) {
            $payment->transactions()->create([
                'account_id'       => $original->account_id,
                'transaction_date' => now(),
                'type'             => 'expense',
                'amount'           => $original->amount,
                'description'      => "Reversal: invoice {$sale->invoice_number}",
                'created_by'       => auth()->id(),
            ]);
        }
    }
}

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function isCredit(): bool
    {
        return in_array($this->type, ["income", "transfer_in"]);

    }

    /**
 * Makes the account ledger match what has been paid on a purchase.
 * Writes only the difference, so it works for create, edit and cancel (pass 0).
 */
public static function syncPurchasePayment(Purchase $purchase, $amountPaid, ?int $accountId = null): ?self
{
    $existing = $purchase->transactions()->get();

    $recorded = bcsub(
        (string) $existing->where('type', 'expense')->sum('amount'),
        (string) $existing->where('type', 'income')->sum('amount'),
        2
    );

    $diff = bcsub((string) ($amountPaid ?? 0), $recorded, 2);

    if (bccomp($diff, '0', 2) === 0) {
        return null; // nothing changed
    }

    // Prefer the given account, then the one already used for this purchase, then Physical Cash.
    $accountId ??= $existing->first()?->account_id;
    $account = $accountId
        ? Account::where('is_active', true)->findOrFail($accountId)
        : Account::where('type', 'cash')->where('is_active', true)->orderBy('id')->first();

    if (! $account) {
        throw new \RuntimeException('No active cash account to pay purchases from.');
    }

    $isPayment = bccomp($diff, '0', 2) > 0;

    return $purchase->transactions()->create([
        'account_id'       => $account->id,
        'transaction_date' => ($existing->isEmpty() ? $purchase->order_date : null) ?? now(),
        'type'             => $isPayment ? 'expense' : 'income',
        'amount'           => ltrim($diff, '-'),
        'description'      => ($isPayment ? 'Payment for purchase #' : 'Adjustment on purchase #') . $purchase->id,
        'created_by'       => auth()->id(),
    ]);
}
}