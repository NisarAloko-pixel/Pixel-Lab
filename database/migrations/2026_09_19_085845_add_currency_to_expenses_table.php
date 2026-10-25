<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('currency', 3)->nullable();
            $table->decimal('exchange_rate', 14, 6)->default(1);
            $table->decimal('original_amount', 14, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate', 'original_amount']);
        });
    }
};