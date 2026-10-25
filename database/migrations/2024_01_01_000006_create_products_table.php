<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table->string("sku")->unique();
            $table->string("name");
            $table->foreignId("category_id")->nullable()->constrained("categories")->nullOnDelete();
            $table->text("description")->nullable();
            $table->decimal("selling_price", 14, 2)->default(0);
            $table->decimal("cost_price", 14, 2)->default(0); // standard/average cost, informational
            $table->decimal("current_stock", 14, 3)->default(0);
            $table->decimal("min_stock_level", 14, 3)->default(0);
            $table->string("unit")->default("pcs");
            $table->string("image_path")->nullable();
            $table->boolean("is_active")->default(true);
            $table->text("notes")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(["is_active"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("products");
    }
};
