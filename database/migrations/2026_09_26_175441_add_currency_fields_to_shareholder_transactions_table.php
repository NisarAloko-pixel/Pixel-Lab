<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shareholder_transactions', function (Blueprint $table) {
            // 'amount' column already stores the converted, base-currency value.
            // These new columns preserve what the shareholder actually paid.
            $table->string('currency', 8)->default('base')->after('amount');
            $table->decimal('original_amount', 12, 2)->nullable()->after('currency');
            $table->decimal('exchange_rate', 12, 6)->default(1)->after('original_amount');
        });
    }

    public function down(): void
    {
        Schema::table('shareholder_transactions', function (Blueprint $table) {
            $table->dropColumn(['currency', 'original_amount', 'exchange_rate']);
        });
    }
};