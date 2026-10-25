<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("sale_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("sale_id")->constrained("sales")->cascadeOnDelete();
            $table->foreignId("product_id")->nullable()->constrained("products")->nullOnDelete();
            $table->foreignId("printing_job_id")->nullable()->constrained("printing_jobs")->nullOnDelete();
            $table->string("description"); // snapshot of product/job name at sale time
            $table->decimal("quantity", 14, 3);
            $table->decimal("unit_price", 14, 2);
            $table->decimal("unit_cost", 14, 2)->default(0); // snapshot of cost at sale time, drives gross profit
            $table->decimal("discount", 14, 2)->default(0);
            $table->decimal("line_total", 14, 2); // (quantity*unit_price) - discount
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("sale_items");
    }
};
