<?php
// Rename with today's timestamp, e.g. 2026_09_15_000002_create_purchases_table.php
// Place in database/migrations/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->enum('item_type', ['supply', 'filament']);

            // Only set when item_type = supply AND you picked an existing supply.
            // Left null for a brand-new supply (created at arrival) or for filament
            // (filament purchases always become a new spool row on arrival).
            $table->foreignId('supply_id')->nullable()->constrained('supplies')->nullOnDelete();

            // Used when item_type = supply and it's a brand-new item (no supply_id yet)
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->nullable();

            // Used when item_type = filament — each purchase becomes its own spool
            $table->string('filament_type')->nullable();
            $table->string('filament_color')->nullable();
            $table->string('filament_brand')->nullable();

            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            $table->decimal('quantity', 14, 2); // pieces/units for supply, grams for filament
            $table->decimal('total_price', 14, 2)->default(0); // total paid for this whole purchase
            $table->decimal('shipping_cost', 14, 2)->default(0); // total shipping for this purchase

            $table->date('order_date')->nullable();

            $table->enum('status', ['in_transit', 'arrived'])->default('in_transit');
            $table->timestamp('arrived_at')->nullable();
            $table->string('arrived_location')->nullable(); // Lab, Home, ...

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};