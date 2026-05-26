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
        Schema::create('destinations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('agency_id')->index();
            $table->string('country_id')->index();
            $table->string('slug')->unique();
            
            $table->jsonb('title_translations');
            $table->jsonb('description_translations');
            
            // Re-integrated spatial coordinates precisely
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->jsonb('map_data')->nullable();
            $table->jsonb('geojson')->nullable();
            $table->jsonb('regional_data')->nullable();
            
            $table->string('country_code');
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};