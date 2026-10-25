<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_increases_product_inventory(): void
    {
        $this->actingAs(User::factory()->create(["role" => User::ROLE_ADMIN]));
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(["current_stock" => 10]);

        app(PurchaseService::class)->create([
            "supplier_id" => $supplier->id,
            "payment_method" => "cash",
            "items" => [[
                "purchasable_type" => "product",
                "purchasable_id" => $product->id,
                "quantity" => 25,
                "unit_cost" => 3.5,
            ]],
        ]);

        $this->assertEquals("35.000", $product->fresh()->current_stock);
    }
}
