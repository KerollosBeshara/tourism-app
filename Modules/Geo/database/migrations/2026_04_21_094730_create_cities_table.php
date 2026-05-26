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
        Schema::create('cities', function (Blueprint $table) {
           $table->string('id')->primary();
            $table->string('country_id')->index();
            
            $table->jsonb('name_translations'); 
            $table->string('slug')->index();
            $table->string('timezone')->nullable();
            
            // Decimal 10,8 and 11,8 are best practices for Lat/Long precision
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->jsonb('meta')->nullable(); // For weather codes, zip patterns, etc.
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
