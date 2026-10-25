<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('status', ['in_transit', 'arrived', 'cancelled'])
                  ->default('in_transit')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('status', ['in_transit', 'arrived'])
                  ->default('in_transit')
                  ->change();
        });
    }
};