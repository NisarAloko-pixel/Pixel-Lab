<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_filaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filament_inventory_id')->constrained('filament_inventory')->cascadeOnDelete();
            $table->decimal('grams_used', 10, 3)->default(0);
            $table->decimal('waste_grams', 10, 3)->default(0); // purge tower + color-change waste
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_filaments');
    }
};