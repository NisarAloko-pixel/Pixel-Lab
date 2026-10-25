<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('grams_used', 10, 2)->nullable()->after('unit');
            $table->decimal('print_hours', 10, 2)->nullable()->after('grams_used');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['grams_used', 'print_hours']);
        });
    }
};