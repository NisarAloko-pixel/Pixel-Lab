<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The payments table is the single source of truth for how much has actually been
 * received or paid out. Sale/Purchase amount_paid & balance columns are denormalized
 * caches refreshed here so dashboards can read them cheaply without re-summing every time.
 */
class PaymentService
{
    public function __construct(private AuditService $audit) {}

    public function recordSalePayment(Sale $sale, float $amount, string $method = "cash", ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($sale, $amount, $method, $notes) {
            $payment = Payment::create([
                "reference_number" => "PAY-".Str::upper(Str::random(8)),
                "paid_at" => now(),
                "amount" => $amount,
                "payment_type" => "sale_payment",
                "payment_method" => $method,
                "payable_type" => Sale::class,
                "payable_id" => $sale->id,
                "notes" => $notes,
                "received_by" => Auth::id(),
            ]);

            app(SaleService::class)->refreshBalance($sale->fresh());

            $this->audit->log("payment.created", $payment, null, $payment->toArray());

            return $payment;
        });
    }

    public function recordPurchasePayment(Purchase $purchase, float $amount, string $method = "cash", ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($purchase, $amount, $method, $notes) {
            $payment = Payment::create([
                "reference_number" => "PAY-".Str::upper(Str::random(8)),
                "paid_at" => now(),
                "amount" => $amount,
                "payment_type" => "purchase_payment",
                "payment_method" => $method,
                "payable_type" => Purchase::class,
                "payable_id" => $purchase->id,
                "notes" => $notes,
                "received_by" => Auth::id(),
            ]);

            $paid = (string) $purchase->payments()->sum("amount");
            $purchase->amount_paid = $paid;
            $purchase->balance = bcsub((string) $purchase->total_cost, $paid, 2);
            $purchase->payment_status = match (true) {
                (float) $purchase->balance <= 0 => "paid",
                (float) $paid > 0 => "partial",
                default => "unpaid",
            };
            $purchase->save();

            $this->audit->log("payment.created", $payment, null, $payment->toArray());

            return $payment;
        });
    }
}
