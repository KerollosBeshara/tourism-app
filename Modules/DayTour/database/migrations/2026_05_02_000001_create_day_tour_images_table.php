<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_tour_images', function (Blueprint $table) {
            $table->id();
            $table->string('day_tour_id')->index();
            $table->text('s3_path')->comment('S3 URL/path for the image');
            
            $table->boolean('is_primary')->default(false)->index();
            $table->integer('sort_order')->default(0);
            
            // Additional metadata for optimization
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('disk')->default('s3')->comment('Storage disk (s3, local)');
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign key with cascade delete
            $table->foreign('day_tour_id')
                ->references('id')
                ->on('day_tours')
                ->cascadeOnDelete();

            // Indexes for performance with millions of rows
            $table->index(['day_tour_id', 'is_primary']);
            $table->index(['day_tour_id', 'sort_order']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_tour_images');
    }
};
