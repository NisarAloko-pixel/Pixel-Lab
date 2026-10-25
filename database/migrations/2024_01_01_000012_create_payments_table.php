<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Single ledger for every money movement in the system (customer payments against
    // sales, and payments made to suppliers against purchases). Source of truth for balances.
    public function up(): void
    {
        Schema::create("payments", function (Blueprint $table) {
            $table->id();
            $table->string("reference_number")->unique();
            $table->date("paid_at");
            $table->decimal("amount", 14, 2);
            $table->string("payment_type"); // sale_payment|purchase_payment
            $table->string("payment_method"); // cash|bank|mobile_money|other
            $table->string("payable_type"); // App\Models\Sale or App\Models\Purchase
            $table->unsignedBigInteger("payable_id");
            $table->text("notes")->nullable();
            $table->foreignId("received_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index(["payable_type", "payable_id"]);
            $table->index(["payment_type", "paid_at"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payments");
    }
};
