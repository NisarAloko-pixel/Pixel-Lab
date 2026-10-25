<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("expenses", function (Blueprint $table) {
            $table->id();
            $table->date("expense_date");
            $table->foreignId("expense_category_id")->constrained("expense_categories")->restrictOnDelete();
            $table->string("description");
            $table->decimal("amount", 14, 2);
            $table->string("payment_method")->default("cash");
            $table->foreignId("supplier_id")->nullable()->constrained("suppliers")->nullOnDelete();
            $table->text("notes")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // business expenses only; personal expenses are out of scope by design

            $table->index("expense_date");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("expenses");
    }
};
