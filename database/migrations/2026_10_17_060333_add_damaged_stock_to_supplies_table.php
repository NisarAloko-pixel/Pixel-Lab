<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('supplies', function (Blueprint $table) {
        $table->decimal('damaged_stock', 12, 2)->default(0)->after('quantity_remaining');
    });
}

public function down(): void
{
    Schema::table('supplies', function (Blueprint $table) {
        $table->dropColumn('damaged_stock');
    });
}
};
