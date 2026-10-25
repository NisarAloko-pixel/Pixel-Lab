<?php
// Rename with today's timestamp, e.g. 2026_09_15_000001_widen_supplies_cost_per_unit.php
// Place in database/migrations/ (must run AFTER the create_supplies_table migration)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            // Was decimal(14,2) — too coarse for cheap-per-unit items (e.g. wire per
            // meter, glue per ml), where the true value rounds straight to 0.00.
            $table->decimal('cost_per_unit', 14, 4)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->decimal('cost_per_unit', 14, 2)->default(0)->change();
        });
    }
};