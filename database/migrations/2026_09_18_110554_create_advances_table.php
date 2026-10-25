<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("advances", function (Blueprint $table) {
            $table->id();
            $table->morphs("party"); // party_type, party_id — e.g. App\Models\Employee
            $table->string("type"); // given (money out to them), repayment (money back to you)
            $table->decimal("amount", 14, 2);
            $table->date("advance_date");
            $table->foreignId("account_id")->constrained("accounts")->restrictOnDelete();
            $table->text("notes")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("advances");
    }
};