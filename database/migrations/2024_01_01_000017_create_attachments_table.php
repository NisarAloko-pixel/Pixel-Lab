<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("attachments", function (Blueprint $table) {
            $table->id();
            $table->string("attachable_type");
            $table->unsignedBigInteger("attachable_id");
            $table->string("path");
            $table->string("original_name")->nullable();
            $table->string("type")->nullable(); // receipt|invoice|reference_image|model_file|other
            $table->foreignId("uploaded_by")->nullable()->constrained("users")->nullOnDelete();
            $table->timestamps();

            $table->index(["attachable_type", "attachable_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("attachments");
    }
};
