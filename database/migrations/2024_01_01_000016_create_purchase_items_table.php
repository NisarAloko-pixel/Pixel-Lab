<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("purchase_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("purchase_id")->constrained("purchases")->cascadeOnDelete();
            $table->string("purchasable_type"); // App\Models\Product or App\Models\FilamentInventory
            $table->unsignedBigInteger("purchasable_id");
            $table->string("description");
            $table->decimal("quantity", 14, 3);
            $table->decimal("unit_cost", 14, 2);
            $table->decimal("line_total", 14, 2);
            $table->timestamps();

            $table->index(["purchasable_type", "purchasable_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("purchase_items");
    }
};
