<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_an_expense(): void
    {
        $user = User::factory()->create(["role" => User::ROLE_MANAGER]);
        $category = ExpenseCategory::factory()->create();

        $response = $this->actingAs($user)->post("/expenses", [
            "expense_date" => now()->toDateString(),
            "expense_category_id" => $category->id,
            "description" => "Filament restock",
            "amount" => 45.50,
            "payment_method" => "cash",
        ]);

        $response->assertRedirect("/expenses");
        $this->assertDatabaseHas("expenses", ["description" => "Filament restock", "amount" => 45.50]);
    }

    public function test_sales_user_cannot_access_expenses(): void
    {
        $user = User::factory()->create(["role" => User::ROLE_SALES]);

        $this->actingAs($user)->get("/expenses")->assertForbidden();
    }
}
