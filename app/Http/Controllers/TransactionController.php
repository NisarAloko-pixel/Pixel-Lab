<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::orderBy('name')->get();

        $transactions = Transaction::with('account', 'reference')
            ->when($request->from, fn ($q) => $q->whereDate('transaction_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('transaction_date', '<=', $request->to))
            ->when($request->account_id, fn ($q) => $q->where('account_id', $request->account_id))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('finance.transaction', compact('transactions', 'accounts'));
    }

    public function create()
    {
        $accounts = Account::orderBy('name')->get();

        return view('finance.transaction-create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'transaction_date' => ['required', 'date'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $validated['created_by'] = auth()->id();

        Transaction::create($validated);

        return redirect()->route('finance.transaction')->with('success', 'Transaction recorded.');
    }

    public function destroy(Transaction $transaction)
    {
        // Guard: don't let manual deletes break linked records (e.g. an Expense's transaction).
        if ($transaction->reference_type) {
            return back()->with('error', 'This transaction is linked to a record and can\'t be deleted here. Delete the original record instead.');
        }

        $transaction->delete();

        return redirect()->route('finance.transaction')->with('success', 'Transaction deleted.');
    }

    public function show(Transaction $transaction)
{
    $transaction->load(['account', 'reference']);

    return view('finance.transaction-show', compact('transaction'));
}
}