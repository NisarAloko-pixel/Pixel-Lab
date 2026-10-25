<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("printing_jobs", function (Blueprint $table) {
            $table->id();
            $table->string("job_number")->unique();
            $table->foreignId("customer_id")->nullable()->constrained("customers")->nullOnDelete();
            $table->string("name");
            $table->text("customer_request")->nullable();
            $table->string("reference_image_path")->nullable();
            $table->string("model_file_path")->nullable();
            $table->string("material")->nullable();
            $table->string("filament_type")->nullable();
            $table->foreignId("filament_inventory_id")->nullable()->constrained("filament_inventory")->nullOnDelete();
            $table->decimal("filament_weight_used_g", 14, 2)->default(0);
            $table->decimal("print_time_hours", 10, 2)->default(0);
            $table->string("machine")->nullable();
            $table->decimal("material_cost", 14, 2)->default(0);
            $table->decimal("electricity_cost", 14, 2)->default(0);
            $table->decimal("labor_cost", 14, 2)->default(0);
            $table->decimal("other_cost", 14, 2)->default(0);
            $table->decimal("total_cost", 14, 2)->default(0); // material+electricity+labor+other
            $table->decimal("selling_price", 14, 2)->default(0);
            $table->decimal("profit", 14, 2)->default(0); // selling_price - total_cost
            $table->string("status")->default("new"); // new|quoted|approved|printing|completed|delivered|cancelled
            $table->text("notes")->nullable();
            $table->foreignId("created_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index("status");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("printing_jobs");
    }
};
