<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->filled("search"), fn ($q) => $q->where("name", "like", "%{$request->search}%"))
            ->orderBy("name")
            ->paginate(20);

        return view("customers.index", compact("customers"));
    }

    public function create()
{
    return view('customers.create');
}
public function edit(Customer $customer)
{
    return view('customers.edit', compact('customer'));
}

public function update(Request $request, Customer $customer)
{
    $data = $request->validate([
        "name" => "required|string",
        "phone" => "nullable|string",
        "email" => "nullable|email",
        "address" => "nullable|string",
        "company" => "nullable|string",
        "notes" => "nullable|string",
    ]);

    $customer->update($data);

    return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
}

public function destroy(Customer $customer)
{
    $customer->delete(); // soft delete — stays in sale history, never hard-removed

    return redirect()->route('customers.index')->with('success', "Customer {$customer->name} deleted.");
}

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string",
            "phone" => "nullable|string",
            "email" => "nullable|email",
            "address" => "nullable|string",
            "company" => "nullable|string",
            "notes" => "nullable|string",
        ]);

        $customer = Customer::create($data);

        return redirect()->route("customers.show", $customer)->with("success", "Customer created.");
    }

    public function show(Customer $customer)
    {
        $customer->load(["sales" => fn ($q) => $q->latest("sold_at"), "printingJobs"]);

        return view("customers.show", compact("customer"));
    }
}
