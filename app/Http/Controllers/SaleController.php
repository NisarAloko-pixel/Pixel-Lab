<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supply;
use App\Services\SaleService;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function __construct(private SaleService $sales) {}

    public function index(Request $request)
    {
        $sales = Sale::with(['customer', 'items.product'])
            ->when($request->filled("from"), fn ($q) => $q->whereDate("sold_at", ">=", $request->from))
            ->when($request->filled("to"), fn ($q) => $q->whereDate("sold_at", "<=", $request->to))
            ->when($request->filled("customer_id"), fn ($q) => $q->where("customer_id", $request->customer_id))
            ->when($request->filled("payment_status"), fn ($q) => $q->where("payment_status", $request->payment_status))
            ->latest("sold_at")
            ->paginate(20)
            ->withQueryString();

        return view("sales.index", [
            "sales" => $sales,
            "displayTimezone" => Setting::get("timezone", config("app.timezone", "UTC")),
        ]);
    }

    public function create()
    {
        return view("sales.create", [
            "customers" => Customer::orderBy("name")->get(),
            "products" => Product::where("is_active", true)->orderBy("name")->get(),
            "supplies" => Supply::orderBy("name")->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->itemRules() + [
            "customer_id" => "nullable|exists:customers,id",
            "sold_at" => "nullable|date",
            "discount" => "nullable|numeric|min:0",
            "tax" => "nullable|numeric|min:0",
            "payment_method" => "required|in:cash,bank,mobile_money,other",
            "amount_paid" => "nullable|numeric|min:0",
            "notes" => "nullable|string",
        ]);

        $sale = $this->sales->create($data);

        return redirect()->route("sales.show", $sale)->with("success", "Invoice {$sale->invoice_number} created.");
    }

    public function show(Sale $sale)
    {
        $sale->load(["items.product", "items.printingJob", "customer", "payments"]);

        return view("sales.show", compact("sale"));
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['customer', 'items', 'payments']);

        $logoPath = Setting::get('business_logo_path', null);

        $business = [
            'name'    => Setting::get('business_name', ''),
            'address' => Setting::get('business_address', ''),
            'phone'   => Setting::get('business_phone', ''),
            'email'   => Setting::get('business_email', ''),
            'logoUrl' => $this->logoDataUri($logoPath),
        ];

        return view('sales.invoice', compact('sale', 'business'));
    }

    public function edit(Sale $sale)
    {
        $sale->load(["items.product", "items.printingJob"]);

        return view("sales.edit", [
            "sale" => $sale,
            "customers" => Customer::orderBy("name")->get(),
            "products" => Product::where("is_active", true)->orderBy("name")->get(),
            "supplies" => Supply::orderBy("name")->get(),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate($this->itemRules() + [
            "customer_id" => "nullable|exists:customers,id",
            "sold_at" => "nullable|date",
            "discount" => "nullable|numeric|min:0",
            "tax" => "nullable|numeric|min:0",
            "payment_method" => "required|in:cash,bank,mobile_money,other",
            "notes" => "nullable|string",
        ]);

        $this->sales->update($sale, $data);

        return redirect()->route("sales.show", $sale)->with("success", "Invoice {$sale->invoice_number} updated.");
    }

    public function destroy(Sale $sale)
    {
        $invoice = $sale->invoice_number;
        $this->sales->delete($sale);

        return redirect()->route("sales.index")->with("success", "Invoice {$invoice} deleted.");
    }

    public function cancel(Request $request, Sale $sale)
    {
        $this->sales->cancel($sale, $request->input("reason"));

        return back()->with("success", "Invoice {$sale->invoice_number} cancelled.");
    }

    /** Shared item-array validation rules used by both store() and update(). */
    private function itemRules(): array
    {
        return [
            "items" => "required|array|min:1",
            "items.*.product_id" => "nullable|exists:products,id",
            "items.*.description" => "nullable|string",
            "items.*.quantity" => "required|numeric|min:0.001",
            "items.*.unit_price" => "required|numeric|min:0",
            "items.*.discount" => "nullable|numeric|min:0",
            // Without this rule validate() drops the Free tick before it reaches SaleService
            "items.*.is_free" => "nullable|boolean",
            "items.*.extras" => "nullable|array",
            "items.*.extras.*.name" => "nullable|string",
            "items.*.extras.*.cost" => "nullable|numeric|min:0",
            "items.*.extras.*.supply_id" => ["nullable", Rule::exists("supplies", "id")->whereNull("deleted_at")],
            "items.*.extras.*.quantity" => "nullable|numeric|min:0.001",
        ];
    }

    private function logoDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($path));
    }
}