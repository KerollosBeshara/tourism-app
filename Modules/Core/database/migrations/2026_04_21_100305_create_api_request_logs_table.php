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
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('agency_id')->index();
            $table->string('actor_id')->nullable()->index(); // account_id (nullable for public routes)
            
            $table->string('method', 10); // GET, POST, etc.
            $table->text('url');
            $table->jsonb('payload')->nullable(); // Request data
            $table->jsonb('response_body')->nullable(); // Response data
            $table->integer('status_code')->index();
            $table->float('duration_ms'); // Performance monitoring
            
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('trace_id')->index(); // Connects related logs
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
