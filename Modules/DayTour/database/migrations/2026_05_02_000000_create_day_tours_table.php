<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_tours', function (Blueprint $table) {
            $table->string('id')->primary(); // ULID
            $table->uuid('agency_id')->index();

            // 1. Define as string first (to match cities.id and destinations.id)
            $table->string('city_id')->index();
            $table->string('destination_id')->index();

            // 2. Set up the foreign key constraints manually
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
            $table->foreign('destination_id')->references('id')->on('destinations')->cascadeOnDelete();

            // Multi-language support
            $table->jsonb('title_translations')->comment('{"en": "...", "ar": "..."}');
            $table->jsonb('description_translations')->comment('{"en": "...", "ar": "..."}');

            // Status columns
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_shared')->default(false)->index();

            $table->softDeletes();
            $table->timestamps();

            // Composite indexes
            $table->index(['agency_id', 'is_active', 'created_at']);
            $table->index(['city_id', 'is_active']);
            $table->index(['destination_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_tours');
    }
};