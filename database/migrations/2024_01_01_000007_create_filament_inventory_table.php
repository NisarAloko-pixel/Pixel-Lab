<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("filament_inventory", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("brand")->nullable();
            $table->string("type")->nullable(); // PLA, ABS, PETG, Resin...
            $table->string("color")->nullable();
            $table->foreignId("supplier_id")->nullable()->constrained("suppliers")->nullOnDelete();
            $table->decimal("weight_purchased_g", 14, 2)->default(0);
            $table->decimal("weight_remaining_g", 14, 2)->default(0);
            $table->decimal("cost_per_kg", 14, 2)->default(0);
            $table->date("purchase_date")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("filament_inventory");
    }
};
