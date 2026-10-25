<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): User
    {
        $user = User::factory()->create(["role" => User::ROLE_ADMIN]);
        $this->actingAs($user);

        return $user;
    }

    public function test_creating_a_sale_generates_a_unique_invoice_number(): void
    {
        $this->actingAdmin();
        $product = Product::factory()->create(["current_stock" => 50, "selling_price" => 10, "cost_price" => 4]);

        $sale1 = app(SaleService::class)->create([
            "payment_method" => "cash",
            "items" => [["product_id" => $product->id, "quantity" => 2, "unit_price" => 10]],
        ]);
        $sale2 = app(SaleService::class)->create([
            "payment_method" => "cash",
            "items" => [["product_id" => $product->id, "quantity" => 1, "unit_price" => 10]],
        ]);

        $this->assertNotEquals($sale1->invoice_number, $sale2->invoice_number);
        $this->assertStringStartsWith("PS-", $sale1->invoice_number);
    }

    public function test_multiple_sale_items_calculate_correct_invoice_total(): void
    {
        $this->actingAdmin();
        $keychain = Product::factory()->create(["selling_price" => 5, "cost_price" => 2, "current_stock" => 100]);
        $clock = Product::factory()->create(["selling_price" => 25, "cost_price" => 10, "current_stock" => 20]);

        $sale = app(SaleService::class)->create([
            "discount" => 5,
            "tax" => 2,
            "payment_method" => "cash",
            "items" => [
                ["product_id" => $keychain->id, "quantity" => 20, "unit_price" => 5],
                ["product_id" => $clock->id, "quantity" => 2, "unit_price" => 25],
            ],
        ]);

        // subtotal = (20*5) + (2*25) = 150; total = 150 - 5 + 2 = 147
        $this->assertEquals("150.00", $sale->subtotal);
        $this->assertEquals("147.00", $sale->total);
        $this->assertEquals("60.00", $sale->cost_total); // (20*2) + (2*10)
    }

    public function test_partial_payment_updates_balance_and_status(): void
    {
        $this->actingAdmin();
        $product = Product::factory()->create(["selling_price" => 100, "cost_price" => 40, "current_stock" => 10]);

        $sale = app(SaleService::class)->create([
            "payment_method" => "cash",
            "amount_paid" => 60,
            "items" => [["product_id" => $product->id, "quantity" => 1, "unit_price" => 100]],
        ]);

        $this->assertEquals("60.00", $sale->amount_paid);
        $this->assertEquals("40.00", $sale->balance);
        $this->assertEquals("partial", $sale->payment_status);
    }

    public function test_sale_decreases_product_inventory(): void
    {
        $this->actingAdmin();
        $product = Product::factory()->create(["current_stock" => 50]);

        app(SaleService::class)->create([
            "payment_method" => "cash",
            "items" => [["product_id" => $product->id, "quantity" => 5, "unit_price" => 10]],
        ]);

        $this->assertEquals("45.000", $product->fresh()->current_stock);
        $this->assertDatabaseHas("inventory_transactions", [
            "trackable_id" => $product->id,
            "type" => "sale",
            "quantity" => -5,
        ]);
    }
}
