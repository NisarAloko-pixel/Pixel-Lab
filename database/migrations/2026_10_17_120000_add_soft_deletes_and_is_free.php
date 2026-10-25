<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // is_free for the "sold free" tick (skipped if you already created it)
        if (Schema::hasTable('sale_items') && ! Schema::hasColumn('sale_items', 'is_free')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->boolean('is_free')->default(false);
            });
        }

        // deleted_at so that deleting a line/recipe row syncs to every machine as an update
        foreach (['sale_items', 'product_filaments'] as $name) {
            if (Schema::hasTable($name) && ! Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['sale_items', 'product_filaments'] as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};