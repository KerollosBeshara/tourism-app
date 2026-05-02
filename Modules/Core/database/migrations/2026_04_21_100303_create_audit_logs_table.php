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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->string('id')->primary(); // ULID is best here for time-sorting
            $table->string('agency_id')->index();
            $table->string('actor_id')->index(); // account_id
            $table->string('actor_type'); // 'staff', 'vendor', 'client', 'super_admin'
            
            $table->string('action'); // e.g., 'created', 'updated', 'deleted'
            $table->string('entity_type')->index(); // e.g., 'App\Models\Post'
            $table->string('entity_id')->index();
            
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent()->index(); // Index for date-range cleanup
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
