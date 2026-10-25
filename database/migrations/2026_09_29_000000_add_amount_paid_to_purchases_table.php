<?php
// Place in database/migrations/2026_09_29_000000_add_amount_paid_to_purchases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Amount paid so far, in the BASE currency.
            $table->decimal('amount_paid', 14, 2)->default(0)->after('exchange_rate');
        });

        // Every existing purchase was paid in full when it was logged.
        // Cancelled ones were refunded, so they stay at 0.
        DB::statement("
            UPDATE purchases
            SET amount_paid = ROUND((total_price + shipping_cost) * exchange_rate, 2)
            WHERE status != 'cancelled'
        ");
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};