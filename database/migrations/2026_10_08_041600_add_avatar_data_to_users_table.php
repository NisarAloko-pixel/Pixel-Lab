<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'avatar_data')) {
            Schema::table('users', fn (Blueprint $t) => $t->longText('avatar_data')->nullable());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'avatar_data')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('avatar_data'));
        }
    }
};