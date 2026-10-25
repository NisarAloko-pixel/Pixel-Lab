<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("transactions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("account_id")->constrained("accounts")->restrictOnDelete();
            $table->date("transaction_date");
            $table->string("type"); // income, expense, transfer_in, transfer_out
            $table->decimal("amount", 14, 2);
            $table->string("description");
            $table->nullableMorphs("reference");
            $table->foreignId("transfer_group_id")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index("transaction_date");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("transactions");
    }
};