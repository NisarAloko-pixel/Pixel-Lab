<?php
// Rename this file to something like: 2026_09_15_000000_create_supplies_table.php
// and place it in database/migrations/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable(); // Wire, Glue, Bulb, Keyring, ...
            $table->string('brand')->nullable();
            $table->string('unit'); // pcs, m, ml, g, kg, roll, box, other
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('quantity_purchased', 14, 2)->default(0);
            $table->decimal('quantity_remaining', 14, 2)->default(0);
            $table->decimal('purchase_price', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('cost_per_unit', 14, 2)->default(0); // landed cost per unit
            $table->decimal('reorder_level', 14, 2)->nullable(); // optional manual low-stock threshold
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};