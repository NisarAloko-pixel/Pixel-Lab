<?php
// Place in app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Supplier;
use App\Support\PurchasePayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        // Purchases are eager-loaded because the balance accessors read them.
        $suppliers = Supplier::with('purchases')
            ->when($request->filled("search"), fn ($q) => $q->where("name", "like", "%{$request->search}%"))
            ->orderBy("name")
            ->paginate(20)
            ->withQueryString();

        return view("suppliers.index", compact("suppliers"));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string",
            "contact_person" => "nullable|string",
            "phone" => "nullable|string",
            "email" => "nullable|email",
            "address" => "nullable|string",
            "notes" => "nullable|string",
        ]);

        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', "Supplier {$supplier->name} created.");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(["purchases.supply", "purchases.transactions.account", "expenses"]);

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        return view("suppliers.show", compact("supplier", "accounts"));
    }

    /**
     * Pay the supplier a lump sum (base currency). It clears the oldest
     * unpaid purchases first and never pays more than is owed.
     */
    public function pay(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $remaining = (string) $data['amount'];
        $totalApplied = '0.00';

        DB::transaction(function () use ($supplier, $data, &$remaining, &$totalApplied) {
            $purchases = $supplier->purchases()
                ->where('status', '!=', 'cancelled')
                ->orderBy('order_date')
                ->orderBy('id')
                ->get();

            foreach ($purchases as $purchase) {
                if (bccomp($remaining, '0', 2) <= 0) {
                    break;
                }
                if (bccomp($purchase->amount_due_base, '0', 2) <= 0) {
                    continue;
                }

                $applied = PurchasePayments::apply(
                    $purchase,
                    $remaining,
                    $data['account_id'],
                    $data['payment_date'] ?? null,
                    $data['notes'] ?? null
                );

                $remaining = bcsub($remaining, $applied, 2);
                $totalApplied = bcadd($totalApplied, $applied, 2);
            }
        });

        if (bccomp($totalApplied, '0', 2) <= 0) {
            return redirect()->route('suppliers.show', $supplier)->with('error', 'Nothing is due to this supplier.');
        }

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Payment of '.number_format((float) $totalApplied, 2).' recorded.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', "Supplier {$supplier->name} deleted.");
    }
}