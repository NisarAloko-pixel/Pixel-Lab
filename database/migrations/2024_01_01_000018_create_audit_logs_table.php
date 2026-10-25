<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Append-only. Nothing in this table is ever updated or deleted by the application.
    public function up(): void
    {
        Schema::create("audit_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->string("action"); // login|sale.created|sale.modified|sale.cancelled|expense.created|...
            $table->string("auditable_type")->nullable();
            $table->unsignedBigInteger("auditable_id")->nullable();
            $table->json("old_values")->nullable();
            $table->json("new_values")->nullable();
            $table->timestamp("created_at")->useCurrent();

            $table->index(["auditable_type", "auditable_id"]);
            $table->index("action");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("audit_logs");
    }
};
