<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Append-only ledger of every stock movement. Never deleted; corrections are new
    // "adjustment"/"reversal" rows, so stock can always be reconstructed from history.
    public function up(): void
    {
        Schema::create("inventory_transactions", function (Blueprint $table) {
            $table->id();
            $table->string("trackable_type"); // App\Models\Product or App\Models\FilamentInventory
            $table->unsignedBigInteger("trackable_id");
            $table->string("type"); // purchase|sale|production_usage|adjustment|return|reversal
            $table->decimal("quantity", 14, 3); // signed: positive = increase, negative = decrease
            $table->decimal("unit_cost", 14, 2)->nullable();
            $table->string("reference_type")->nullable(); // Sale, Purchase, PrintingJob, etc.
            $table->unsignedBigInteger("reference_id")->nullable();
            $table->text("notes")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index(["trackable_type", "trackable_id"]);
            $table->index(["reference_type", "reference_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("inventory_transactions");
    }
};
