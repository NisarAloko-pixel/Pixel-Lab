<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Support\Currencies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class BalanceController extends Controller
{
    public function index()
    {
        $accounts = Account::where("is_active", true)->orderBy("name")->get();

        $baseCurrency = Setting::get("currency", "USD");

// One total per currency; accounts without a currency count as the base currency.
$totalsByCurrency = $accounts
    ->groupBy(fn ($account) => $account->currency ?: $baseCurrency)
    ->map(fn ($group) => $group->reduce(
        fn ($carry, $account) => bcadd($carry, $account->current_balance, 2),
        "0"
    ))
    ->sortKeys();

        // Last 30 days, grouped by day. Transfers between your own accounts are left out
        // of In/Out so the columns show real money coming in and going out.
        $daily = Transaction::selectRaw("transaction_date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as in_amount,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as out_amount")
            ->where("transaction_date", ">=", now()->subDays(30))
            ->groupBy("transaction_date")
            ->orderBy("transaction_date")
            ->get();

        // Every individual transaction behind those daily totals.
        $transactions = Transaction::with("account")
            ->where("transaction_date", ">=", now()->subDays(30))
            ->orderByDesc("transaction_date")
            ->orderByDesc("id")
            ->get();

        return view("finance.balance", [
    "accounts" => $accounts,
    "totalsByCurrency" => $totalsByCurrency,
    "baseCurrency" => $baseCurrency,
    "daily" => $daily,
    "transactions" => $transactions,
    "currencies" => Currencies::LIST,
]);
    }

    public function transfer(Request $request)
{
    $validated = $request->validate([
        "from_account_id" => ["required", "exists:accounts,id", "different:to_account_id"],
        "to_account_id" => ["required", "exists:accounts,id"],
        "amount" => ["required", "numeric", "min:0.01"],
        "exchange_rate" => ["nullable", "numeric", "min:0.000001"],
        "transaction_date" => ["required", "date"],
        "description" => ["nullable", "string", "max:255"],
    ]);

    $base = Setting::get("currency", "USD");
    $from = Account::findOrFail($validated["from_account_id"]);
    $to = Account::findOrFail($validated["to_account_id"]);
    $fromCur = $from->currency ?: $base;
    $toCur = $to->currency ?: $base;
    $cross = $fromCur !== $toCur;

    if ($cross && empty($validated["exchange_rate"])) {
        return back()->withInput()
            ->with("error", "These accounts use different currencies. Enter the exchange rate: 1 {$fromCur} = ? {$toCur}.");
    }

    $sent = (string) $validated["amount"];
    $received = $cross ? bcmul($sent, (string) $validated["exchange_rate"], 2) : $sent;

    $note = trim($validated["description"] ?? "");
    if ($cross) {
        $rateText = "1 {$fromCur} = " . rtrim(rtrim(number_format((float) $validated["exchange_rate"], 6, ".", ""), "0"), ".") . " {$toCur}";
        $note = $note !== "" ? "{$note} · {$rateText}" : $rateText;
    }

    DB::transaction(function () use ($validated, $sent, $received, $note) {
        $out = Transaction::create([
            "account_id" => $validated["from_account_id"],
            "transaction_date" => $validated["transaction_date"],
            "type" => "transfer_out",
            "amount" => $sent,
            "description" => $note !== "" ? $note : "Transfer to account",
            "created_by" => auth()->id(),
        ]);

        // The outgoing entry's id is unique, so it makes a safe pairing key.
        $out->update(["transfer_group_id" => $out->id]);

        Transaction::create([
            "account_id" => $validated["to_account_id"],
            "transaction_date" => $validated["transaction_date"],
            "type" => "transfer_in",
            "amount" => $received,
            "description" => $note !== "" ? $note : "Transfer from account",
            "transfer_group_id" => $out->id,
            "created_by" => auth()->id(),
        ]);
    });

    return redirect()->route("finance.balance")->with("success", "Transfer recorded.");
}

    /** Manually adds money to an account (recorded as an income entry). */
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            "account_id" => ["required", "exists:accounts,id"],
            "amount" => ["required", "numeric", "min:0.01"],
            "transaction_date" => ["required", "date"],
            "description" => ["nullable", "string", "max:255"],
        ]);

        Transaction::create([
            "account_id" => $validated["account_id"],
            "transaction_date" => $validated["transaction_date"],
            "type" => "income",
            "amount" => $validated["amount"],
            "description" => $validated["description"] ?? "Balance added",
            "created_by" => auth()->id(),
        ]);

        return redirect()->route("finance.balance")->with("success", "Balance added.");
    }

    /** Manually deducts money from an account (recorded as an expense entry). Negative balances are allowed. */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            "account_id" => ["required", "exists:accounts,id"],
            "amount" => ["required", "numeric", "min:0.01"],
            "transaction_date" => ["required", "date"],
            "description" => ["nullable", "string", "max:255"],
        ]);

        $account = Account::findOrFail($validated["account_id"]);

        Transaction::create([
            "account_id" => $account->id,
            "transaction_date" => $validated["transaction_date"],
            "type" => "expense",
            "amount" => $validated["amount"],
            "description" => $validated["description"] ?? "Balance deducted",
            "created_by" => auth()->id(),
        ]);

        return redirect()->route("finance.balance")->with("success", "Balance deducted from {$account->name}.");
    }
/**
 * Sets an account to an exact balance WITHOUT recording a transaction.
 * balance = opening_balance + in - out, so we solve for opening_balance.
 */
public function adjust(Request $request)
{
    $validated = $request->validate([
        "account_id" => ["required", "exists:accounts,id"],
        "new_balance" => ["required", "numeric"],
    ]);

    $account = Account::findOrFail($validated["account_id"]);

    $in = $account->transactions()->whereIn("type", ["income", "transfer_in"])->sum("amount");
    $out = $account->transactions()->whereIn("type", ["expense", "transfer_out"])->sum("amount");
    $movement = bcsub((string) $in, (string) $out, 2);

    $account->update([
        "opening_balance" => bcsub((string) $validated["new_balance"], $movement, 2),
    ]);

    return redirect()->route("finance.balance")
        ->with("success", "{$account->name} balance set to " . number_format((float) $validated["new_balance"], 2) . ".");
}

    /** Edits a manually added entry (from "Add balance", "Deduct balance" or an adjustment). */
    public function updateEntry(Request $request, Transaction $transaction)
    {
        $isManual = $transaction->reference_type === null
            && in_array($transaction->type, ["income", "expense"], true);

        if (! $isManual) {
            return redirect()->route("finance.balance")
                ->with("error", "Only manual entries can be edited here. Edit payments, expenses and purchases from their own pages.");
        }

        $validated = $request->validate([
            "account_id" => ["required", "exists:accounts,id"],
            "amount" => ["required", "numeric", "min:0.01"],
            "transaction_date" => ["required", "date"],
            "description" => ["nullable", "string", "max:255"],
        ]);

        $transaction->update([
            "account_id" => $validated["account_id"],
            "amount" => $validated["amount"],
            "transaction_date" => $validated["transaction_date"],
            "description" => $validated["description"] ?? $transaction->description,
        ]);

        return redirect()->route("finance.balance")->with("success", "Entry updated.");
    }

    /** Renames an account and/or changes its currency label. */
    public function updateAccount(Request $request, Account $account)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "currency" => ["nullable", "string", "in:" . implode(",", array_keys(Currencies::LIST))],
        ]);

        $account->update($validated);

        return redirect()->route("finance.balance")->with("success", "Account updated.");
    }

    /** Creates a new account. */
public function storeAccount(Request $request)
{
    $validated = $request->validate([
        "name" => ["required", "string", "max:255"],
        "type" => ["required", "in:cash,bank"],
        "currency" => ["nullable", "string", "in:" . implode(",", array_keys(Currencies::LIST))],
        "opening_balance" => ["nullable", "numeric"],
    ]);

    $account = Account::create([
        "name" => trim($validated["name"]),
        "type" => $validated["type"],
        "currency" => $validated["currency"] ?? null,
        "opening_balance" => $validated["opening_balance"] ?? 0,
        "is_active" => true,
    ]);

    return redirect()->route("finance.balance")->with("success", "{$account->name} created.");
}

/**
 * Removes an account. It must be at 0.00 so no money disappears from the totals.
 * An account with transaction history is archived (hidden, history kept) instead
 * of deleted, so old payments and reports still point to it.
 */
public function destroyAccount(Account $account)
{
    if (bccomp((string) $account->current_balance, "0", 2) !== 0) {
        return back()->with("error", "{$account->name} still has a balance of " . number_format((float) $account->current_balance, 2) . ". Transfer it out or set it to 0 first.");
    }

    // Sale and purchase payments are posted to the first active cash/bank account.
    if (in_array($account->type, ["cash", "bank"], true)) {
        $another = Account::where("type", $account->type)
            ->where("is_active", true)
            ->where("id", "!=", $account->id)
            ->exists();

        if (! $another) {
            return back()->with("error", "Keep at least one active {$account->type} account, because payments are posted to it.");
        }
    }

    if ($account->transactions()->exists()) {
        $account->update(["is_active" => false]);

        return redirect()->route("finance.balance")
            ->with("success", "{$account->name} has transaction history, so it was archived (hidden) instead of deleted.");
    }

    $account->delete();

    return redirect()->route("finance.balance")->with("success", "{$account->name} deleted.");
}
}