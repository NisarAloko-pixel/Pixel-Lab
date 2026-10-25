<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip safely if this was already added by hand at some point.
        if (Schema::hasColumn('products', 'electricity_rate_per_hour')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('electricity_rate_per_hour', 8, 2)
                ->default(0)
                ->after('print_hours');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('electricity_rate_per_hour');
        });
    }
};