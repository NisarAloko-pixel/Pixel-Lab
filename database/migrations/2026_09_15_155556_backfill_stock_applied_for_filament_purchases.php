<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchases')
            ->where('item_type', 'filament')
            ->where('status', 'arrived')
            ->update(['stock_applied' => true, 'stock_applied_at' => DB::raw('arrived_at')]);
    }

    public function down(): void
    {
        // No safe reverse — backfill is one-directional.
    }
};