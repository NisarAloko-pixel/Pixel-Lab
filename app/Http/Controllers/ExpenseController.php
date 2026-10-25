<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Support\Currencies;

class ExpenseController extends Controller
{
    /** Built-in categories, created automatically the first time the table is empty. */
    private const DEFAULT_CATEGORIES = [
        'Ads',
        'Electricity',
        'Equipment',
        'Maintenance',
        'Transportation',
        'Marketing',
        'Shop/Office',
        'Other',
    ];

    /**
     * Seed the default categories only when there are none at all, so categories
     * you rename or delete later don't keep coming back.
     */
    private function categories()
    {
        if (ExpenseCategory::count() === 0) {
            foreach (self::DEFAULT_CATEGORIES as $name) {
                ExpenseCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
            }
        }

        return ExpenseCategory::orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $categories = $this->categories();

        $expenses = Expense::with('category', 'account')
            ->when($request->from, fn ($q) => $q->whereDate('expense_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('expense_date', '<=', $request->to))
            ->when($request->category_id, fn ($q) => $q->where('expense_category_id', $request->category_id))
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function create()
{
    $categories = $this->categories();
    $suppliers = Supplier::orderBy('name')->get();
    $accounts = Account::where('is_active', true)->orderBy('name')->get();
    $currencies = Currencies::LIST;
    $baseCurrency = Setting::get('currency', 'USD');

    return view('expenses.create', compact('categories', 'suppliers', 'accounts', 'currencies', 'baseCurrency'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'expense_date' => ['required', 'date'],
        'expense_category_id' => ['required', 'exists:expense_categories,id'],
        'account_id' => ['required', 'exists:accounts,id'],
        'description' => ['required', 'string', 'max:255'],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'currency' => ['required', 'string', 'max:3'],
        'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        'payment_method' => ['required', 'in:cash,bank,mobile_money,other'],
        'supplier_id' => ['nullable', 'exists:suppliers,id'],
        'notes' => ['nullable', 'string'],
    ]);

    $validated = $this->withCurrency($validated);
    $validated['created_by'] = auth()->id();

    $expense = Expense::create($validated);

    $expense->transaction()->create([
        'account_id' => $expense->account_id,
        'transaction_date' => $expense->expense_date,
        'type' => 'expense',
        'amount' => $expense->amount, // base currency
        'description' => $expense->description,
        'created_by' => auth()->id(),
    ]);

    return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
}

public function edit(Expense $expense)
{
    $categories = $this->categories();
    $suppliers = Supplier::orderBy('name')->get();
    $accounts = Account::where('is_active', true)->orderBy('name')->get();
    $currencies = Currencies::LIST;
    $baseCurrency = Setting::get('currency', 'USD');

    return view('expenses.edit', compact('expense', 'categories', 'suppliers', 'accounts', 'currencies', 'baseCurrency'));
}

public function update(Request $request, Expense $expense)
{
    $validated = $request->validate([
        'expense_date' => ['required', 'date'],
        'expense_category_id' => ['required', 'exists:expense_categories,id'],
        'account_id' => ['required', 'exists:accounts,id'],
        'description' => ['required', 'string', 'max:255'],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'currency' => ['required', 'string', 'max:3'],
        'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        'payment_method' => ['required', 'in:cash,bank,mobile_money,other'],
        'supplier_id' => ['nullable', 'exists:suppliers,id'],
        'notes' => ['nullable', 'string'],
    ]);

    $expense->update($this->withCurrency($validated));

    $expense->transaction()->updateOrCreate([], [
        'account_id' => $expense->account_id,
        'transaction_date' => $expense->expense_date,
        'type' => 'expense',
        'amount' => $expense->amount,
        'description' => $expense->description,
    ]);

    return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated.');
}
    /**
     * Quick-add a category (used by the "+ New category" link on the expense form).
     * Returns JSON for fetch() calls, or redirects back for a normal form post.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name'],
        ]);

        $category = ExpenseCategory::create([
            'name' => trim($validated['name']),
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $category->id, 'name' => $category->name], 201);
        }

        return back()->with('success', 'Category added.');
    }

    public function show(Expense $expense)
    {
        $expense->load('category', 'supplier', 'account', 'creator');

        return view('expenses.show', compact('expense'));
    }

   

    public function destroy(Expense $expense)
    {
        $expense->transaction()->delete();
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    private function withCurrency(array $validated): array
{
    $base = Setting::get('currency', 'USD');

    if ($validated['currency'] === $base) {
        $validated['exchange_rate'] = 1;
    }

    $validated['original_amount'] = $validated['amount'];
    $validated['amount'] = bcmul((string) $validated['amount'], (string) $validated['exchange_rate'], 2);

    return $validated;
}
}