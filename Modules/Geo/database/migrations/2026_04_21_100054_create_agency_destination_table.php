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
        Schema::create('agency_destination', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('agency_id')->index();
            $table->string('destination_id')->index();
            
            // Unique features for this agency's view
            $table->boolean('is_featured')->default(false)->index(); 
            $table->decimal('custom_price_modifier', 12, 2)->default(0.00); 
            
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');

            // Performance: Prevent duplicate mapping and speed up lookups
            $table->unique(['agency_id', 'destination_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_destination');
    }
};
