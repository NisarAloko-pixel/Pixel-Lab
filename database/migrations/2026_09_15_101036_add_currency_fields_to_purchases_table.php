<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('price_per_unit', 14, 4)->nullable()->after('quantity');
            $table->string('currency', 3)->default('USD')->after('shipping_cost');
            $table->decimal('exchange_rate', 14, 6)->default(1)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['price_per_unit', 'currency', 'exchange_rate']);
        });
    }
};