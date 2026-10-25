<?php
// Place in app/Http/Controllers/AdvanceController.php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Advance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Shareholder;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Support\Currencies;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::orderBy('name')->get()
            ->filter(fn ($c) => (float) $c->outstanding_balance != 0);

        // All suppliers (so new ones sync immediately). Purchases are eager-loaded
        // because the balance accessors read them.
        $suppliers = Supplier::with('purchases')->orderBy('name')->get();

        $employees = Employee::where('is_active', true)->orderBy('name')->get(); // no column restriction — balance is an accessor

        $accounts = Account::where('is_active', true)->orderBy('name')->get(['id', 'name']);

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
        $totalProfit = $totalIncome - $totalExpense;

        return view('finance.advance', [
            'customers' => $customers,
            'suppliers' => $suppliers,
            'employees' => $employees,
            'accounts' => $accounts,
            'shareholders' => $shareholders,
            'totalProfit' => $totalProfit,
            'baseCurrency' => Setting::get('currency', 'USD'),
            'supportedCurrencies' => array_keys(Currencies::LIST),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'in:given,repayment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'advance_date' => ['required', 'date'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $advance = $employee->advances()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'advance_date' => $validated['advance_date'],
            'account_id' => $validated['account_id'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // "given" = cash left the business (expense); "repayment" = cash came back (income)
        $advance->transaction()->create([
            'account_id' => $advance->account_id,
            'transaction_date' => $advance->advance_date,
            'type' => $advance->type === 'given' ? 'expense' : 'income',
            'amount' => $advance->amount,
            'description' => ($advance->type === 'given' ? 'Advance to ' : 'Repayment from ').$employee->name,
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            ]);
        }

        return redirect()->route('finance.advance')->with('success', 'Recorded.');
    }

    public function destroy(Advance $advance)
    {
        $advance->transaction()->delete();
        $advance->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            ]);
        }

        return redirect()->route('finance.advance')->with('success', 'Entry removed.');
    }

    public function employeeTransactions(Employee $employee)
    {
        $transactions = $employee->advances()
            ->with('account')
            ->orderByDesc('advance_date')
            ->orderByDesc('id')
            ->get();

        $given = $transactions->where('type', 'given')->sum('amount');
        $repaid = $transactions->where('type', 'repayment')->sum('amount');

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        return view('finance.employee-transactions', compact(
            'employee', 'transactions', 'given', 'repaid', 'accounts'
        ));
    }
}