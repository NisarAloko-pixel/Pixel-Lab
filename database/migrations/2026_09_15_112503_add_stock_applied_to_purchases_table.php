<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('stock_applied')->default(false)->after('status');
            $table->timestamp('stock_applied_at')->nullable()->after('stock_applied');
        });

        // Backfill: purchases that already arrived AND already had a supply
        // linked were auto-applied under the old logic — mark them as such so
        // they don't reappear as pending. Ones still missing a supply_id were
        // never applied, so they correctly stay pending.
        DB::table('purchases')
            ->where('item_type', 'supply')
            ->where('status', 'arrived')
            ->whereNotNull('supply_id')
            ->update(['stock_applied' => true, 'stock_applied_at' => DB::raw('arrived_at')]);
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['stock_applied', 'stock_applied_at']);
        });
    }
};