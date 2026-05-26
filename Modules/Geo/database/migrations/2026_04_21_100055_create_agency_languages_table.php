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
        Schema::create('agency_languages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('agency_id')->index();
            $table->string('language_id')->index();
            
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Ensure an agency can't have the same language twice
            $table->unique(['agency_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_languages');
    }
};
