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
        Schema::create('agency_statuses', function (Blueprint $table) {
            $table->string('id')->primary(); // ULID/UUID
            $table->jsonb('name_translations'); // Multi-language names
            $table->string('color_code', 7)->nullable(); // Hex code e.g., #FFFFFF
            $table->integer('sort_order')->default(0)->index(); // For UI sorting
            $table->boolean('is_active')->default(true)->index(); // Quick filtering
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_statuses');
    }
};
