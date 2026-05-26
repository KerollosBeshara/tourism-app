<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('destination_tourism_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('destination_id')->index(); // Changed from foreignUuid to string
            
            $table->integer('sort_order')->default(0);
            $table->string('icon')->nullable();
            
            $table->jsonb('title_translations');
            $table->jsonb('description_translations');
            
            $table->timestamps();

            $table->foreign('destination_id')
                  ->references('id')
                  ->on('destinations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_tourism_items');
    }
};