<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class SalePaymentController extends Controller
{
    public function store(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->balance,
            'payment_method' => 'required|in:cash,bank,mobile_money,other',
        ]);

        DB::transaction(function () use ($data, $sale) {
    $payment = $sale->payments()->create([
        'reference_number' => $this->nextReferenceNumber(),
        'payment_type' => 'income',
        'amount' => $data['amount'],
        'payment_method' => $data['payment_method'],
        'paid_at' => now(),
        'received_by' => auth()->id(),
    ]);

    Transaction::recordSalePayment($payment, $sale);

    $newAmountPaid = bcadd((string) $sale->amount_paid, (string) $data['amount'], 2);
    $newBalance = bcsub((string) $sale->total, $newAmountPaid, 2);

    $sale->update([
        'amount_paid' => $newAmountPaid,
        'balance' => $newBalance,
        'payment_status' => $newBalance <= 0 ? 'paid' : ($newAmountPaid > 0 ? 'partial' : 'unpaid'),
    ]);
});

        return back()->with('success', 'Payment recorded.');
    }

   private function nextReferenceNumber(): string
{
    $last = Payment::where('reference_number', 'like', 'PAY-%')
        ->orderByRaw("CAST(SUBSTR(reference_number, 5) AS INTEGER) DESC")
        ->lockForUpdate()
        ->value('reference_number');

    $lastNumber = $last ? (int) substr($last, 4) : 0;

    return 'PAY-'.str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
}
}