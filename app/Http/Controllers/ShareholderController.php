<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Setting;
use App\Models\Shareholder;
use App\Models\ShareholderTransaction;
use App\Models\Transaction;
use App\Support\Currencies;
use Illuminate\Http\Request;

class ShareholderController extends Controller
{
    protected function baseCurrency(): string
    {
        return Setting::get('currency', 'USD');
    }

    protected function supportedCurrencies(): array
    {
        return array_keys(Currencies::LIST);
    }

    protected function shareholdersPayload(): array
    {
        $shareholders = Shareholder::where('is_active', true)->orderByDesc('share_percentage')->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'role' => $s->role,
                'share_percentage' => (float) $s->share_percentage,
                'total_invested' => (float) $s->total_invested,
            ]);

        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');

        return [
            'shareholders' => $shareholders,
            'totalProfit' => (float) ($totalIncome - $totalExpense),
        ];
    }

    public function store(Request $request)
    {
        $baseCurrency = $this->baseCurrency();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'share_percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'initial_investment' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'in:'.implode(',', $this->supportedCurrencies())],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'account_id' => ['required', 'exists:accounts,id'],
            'invested_at' => ['required', 'date'],
        ]);

        $existingTotal = Shareholder::where('is_active', true)->sum('share_percentage');

        if ($existingTotal + $validated['share_percentage'] > 100) {
            $message = 'Total shares would exceed 100%. Available: '.number_format(100 - $existingTotal, 2).'%';

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => ['share_percentage' => [$message]],
                ], 422);
            }

            return back()->withErrors(['share_percentage' => $message])->withInput();
        }

        $convertedAmount = $validated['currency'] === $baseCurrency
            ? $validated['initial_investment']
            : $validated['initial_investment'] * $validated['exchange_rate'];

        $shareholder = Shareholder::create([
            'name' => $validated['name'],
            'role' => $validated['role'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'share_percentage' => $validated['share_percentage'],
            'is_active' => true,
        ]);

        $txn = Transaction::create([
            'account_id' => $validated['account_id'],
            'transaction_date' => $validated['invested_at'],
            'type' => 'income',
            'amount' => $convertedAmount,
            'description' => 'Initial capital investment from '.$shareholder->name,
            'created_by' => auth()->id(),
        ]);

        $shareholder->transactions()->create([
            'type' => 'investment',
            'amount' => $convertedAmount,
            'currency' => $validated['currency'],
            'original_amount' => $validated['initial_investment'],
            'exchange_rate' => $validated['currency'] === $baseCurrency ? 1 : $validated['exchange_rate'],
            'transaction_date' => $validated['invested_at'],
            'account_id' => $validated['account_id'],
            'transaction_id' => $txn->id,
            'notes' => 'Initial investment',
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json($this->shareholdersPayload());
        }

        return redirect()->route('finance.advance')->with('success', 'Shareholder added.');
    }

    public function destroy(Shareholder $shareholder)
    {
        foreach ($shareholder->transactions as $t) {
            if ($t->transaction_id) {
                Transaction::whereKey($t->transaction_id)->delete();
            }
        }

        $shareholder->delete();

        if (request()->wantsJson()) {
            return response()->json($this->shareholdersPayload());
        }

        return redirect()->route('finance.advance')->with('success', 'Shareholder removed.');
    }

    public function storeTransaction(Request $request)
    {
        $baseCurrency = $this->baseCurrency();

        $validated = $request->validate([
            'shareholder_id' => ['required', 'exists:shareholders,id'],
            'type' => ['required', 'in:investment,dividend'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'in:'.implode(',', $this->supportedCurrencies())],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'account_id' => ['required', 'exists:accounts,id'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $shareholder = Shareholder::findOrFail($validated['shareholder_id']);

        $convertedAmount = $validated['currency'] === $baseCurrency
            ? $validated['amount']
            : $validated['amount'] * $validated['exchange_rate'];

        $txn = Transaction::create([
            'account_id' => $validated['account_id'],
            'transaction_date' => $validated['transaction_date'],
            'type' => $validated['type'] === 'investment' ? 'income' : 'expense',
            'amount' => $convertedAmount,
            'description' => ($validated['type'] === 'investment' ? 'Capital investment from ' : 'Dividend paid to ').$shareholder->name,
            'created_by' => auth()->id(),
        ]);

        $shareholder->transactions()->create([
            'type' => $validated['type'],
            'amount' => $convertedAmount,
            'currency' => $validated['currency'],
            'original_amount' => $validated['amount'],
            'exchange_rate' => $validated['currency'] === $baseCurrency ? 1 : $validated['exchange_rate'],
            'transaction_date' => $validated['transaction_date'],
            'account_id' => $validated['account_id'],
            'transaction_id' => $txn->id,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json($this->shareholdersPayload());
        }

        return redirect()->route('finance.advance')->with('success', 'Recorded.');
    }

    public function destroyTransaction(ShareholderTransaction $shareholderTransaction)
    {
        if ($shareholderTransaction->transaction_id) {
            Transaction::whereKey($shareholderTransaction->transaction_id)->delete();
        }

        $shareholderTransaction->delete();

        if (request()->wantsJson()) {
            return response()->json($this->shareholdersPayload());
        }

        return back()->with('success', 'Entry removed.');
    }

    public function transactions(Shareholder $shareholder)
    {
        $transactions = $shareholder->transactions()
            ->with('account')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $totalProfit = $totalIncome - $totalExpense;

        $profitShare = $shareholder->profitShare($totalProfit);
        $availableProfitShare = $shareholder->availableProfitShare($totalProfit);

        return view('finance.shareholder-transactions', [
            'shareholder' => $shareholder,
            'transactions' => $transactions,
            'accounts' => $accounts,
            'totalProfit' => $totalProfit,
            'profitShare' => $profitShare,
            'availableProfitShare' => $availableProfitShare,
            'baseCurrency' => $this->baseCurrency(),
            'supportedCurrencies' => $this->supportedCurrencies(),
        ]);
    }
}