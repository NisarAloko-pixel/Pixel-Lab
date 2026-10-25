<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filament_inventory', function (Blueprint $table) {
            $table->decimal('purchase_price', 14, 2)->default(0)->after('weight_purchased_g');
            $table->decimal('shipping_cost', 14, 2)->default(0)->after('purchase_price');
        });
    }

    public function down(): void
    {
        Schema::table('filament_inventory', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'shipping_cost']);
        });
    }
};