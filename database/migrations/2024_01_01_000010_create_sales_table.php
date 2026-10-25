<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("sales", function (Blueprint $table) {
            $table->id();
            $table->string("invoice_number")->unique();
            $table->foreignId("customer_id")->nullable()->constrained("customers")->nullOnDelete();
            $table->dateTime("sold_at");
            $table->decimal("subtotal", 14, 2)->default(0);
            $table->decimal("discount", 14, 2)->default(0);
            $table->decimal("tax", 14, 2)->default(0);
            $table->decimal("total", 14, 2)->default(0);        // subtotal - discount + tax
            $table->decimal("cost_total", 14, 2)->default(0);   // sum of sale_items cost at time of sale
            $table->decimal("amount_paid", 14, 2)->default(0);  // denormalized cache, source of truth is payments table
            $table->decimal("balance", 14, 2)->default(0);      // total - amount_paid
            $table->string("payment_status")->default("unpaid"); // paid|partial|unpaid
            $table->string("payment_method")->nullable();
            $table->string("status")->default("completed"); // completed|cancelled
            $table->text("notes")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // cancelled sales are soft-deleted, never hard-deleted; audit_logs keep full history

            $table->index(["sold_at"]);
            $table->index(["payment_status"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("sales");
    }
};
