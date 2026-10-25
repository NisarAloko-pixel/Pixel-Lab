<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shareholder_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shareholder_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['investment', 'dividend']); // investment = money in, dividend = money out
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->foreignId('account_id')->nullable()->constrained();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shareholder_transactions');
    }
};