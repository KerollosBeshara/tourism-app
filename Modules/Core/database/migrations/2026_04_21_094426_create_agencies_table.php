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
        Schema::create('agencies', function (Blueprint $table) {
           $table->string('id')->primary();
            
            // Foreign Keys
            $table->string('agency_status_id')->index();
            $table->string('country_id')->index();
            $table->string('base_currency_id')->index();
            
            // Basic Info
            $table->string('name');
            $table->string('slug')->unique(); // For URLs
            $table->string('logo_path')->nullable();
            $table->string('brand_color', 7)->nullable();
            
            // Contact & Config
            $table->string('contact_email')->index();
            $table->string('contact_phone', 20)->nullable();
            $table->text('official_address')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('DD/MM/YYYY');
            
            // Advanced Data
            $table->jsonb('social_links')->nullable();
            $table->string('account_manager_id')->nullable()->index(); // Refers to a Super Admin Account
            $table->boolean('is_active')->default(true)->index();
            
            $table->timestamps();
            $table->softDeletes(); // Millions of rows: Soft deletes help with audits

            // Constraints
            $table->foreign('agency_status_id')->references('id')->on('agency_statuses');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('base_currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
