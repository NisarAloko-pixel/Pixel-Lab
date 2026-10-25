<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'prime_tower_grams')) {
                $table->decimal('prime_tower_grams', 8, 2)->default(0)->after('grams_used');
            }
            if (!Schema::hasColumn('products', 'units_per_plate')) {
                $table->unsignedInteger('units_per_plate')->default(1)->after('prime_tower_grams');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'prime_tower_grams')) {
                $table->dropColumn('prime_tower_grams');
            }
            if (Schema::hasColumn('products', 'units_per_plate')) {
                $table->dropColumn('units_per_plate');
            }
        });
    }
};