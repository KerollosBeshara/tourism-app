<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->string('id', 36)->primary(); // ULID
            
            // System level type
            $table->string('type', 20)->default('image'); 
            $table->text('file_path'); 
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); 
            
            // Contextual collections
            $table->string('collection_name', 50)->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            
            // Polymorphic Relations
            $table->string('mediable_type', 150)->nullable();
            $table->string('mediable_id', 36)->nullable(); // Matches your target ULIDs
            
            $table->timestamps();

            // High performance query index
            $table->index(['mediable_type', 'mediable_id', 'collection_name'], 'idx_media_polymorphic_collection');
            $table->index(['sort_order']);
        });

        // 1. Updated Engine Check Constraint: Allows files or links cleanly
        DB::statement("
            ALTER TABLE media 
            ADD CONSTRAINT check_media_type 
            CHECK (type IN ('image', 'video_link', 'video_file', 'document'))
        ");

        // 2. Singleton Enforcement: Ensures exactly ONE avatar/banner/featured per entity
        DB::statement("
            CREATE UNIQUE INDEX idx_media_unique_singleton_collections 
            ON media (mediable_type, mediable_id, collection_name) 
            WHERE collection_name IN ('featured', 'avatar', 'banner')
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};