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
        Schema::create('countries', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('iso_code', 3)->unique()->index(); // ISO 3166-1 alpha-2 or alpha-3
            $table->string('emoji_flag', 10)->nullable();
            $table->jsonb('name_translations'); // Optimized for PostGreSQL/MySQL 8+
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
