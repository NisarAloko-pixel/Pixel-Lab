<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_items', 'is_free')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->boolean('is_free')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_items', 'is_free')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn('is_free');
            });
        }
    }
};