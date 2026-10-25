<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("accounts", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("type"); // cash, bank, paypal, other
            $table->decimal("opening_balance", 14, 2)->default(0);
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("accounts");
    }
};