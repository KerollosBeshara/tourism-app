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
        Schema::create('destination_media', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('destination_id')->index(); // Enforce string matching destinations.id
            
            $table->string('type'); // 'image', 'video_link', 'video_file'
            $table->text('url'); // S3 URL for images/videos OR external link for video_link
            
            // Core file tracking columns (nullable to accommodate external video links)
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('disk')->default('s3');
            
            // Meta column holds layout variations like ['thumbnail_url' => '...', 'medium_url' => '...']
            $table->jsonb('meta')->nullable();
            $table->jsonb('caption_translations')->nullable();
            
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            
            $table->timestamps();

            $table->foreign('destination_id')
                  ->references('id')
                  ->on('destinations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_media');
    }
};