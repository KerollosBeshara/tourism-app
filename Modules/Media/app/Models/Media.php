<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids; // ⚡ Added for server-side automatic ULID generation
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasUlids; // ⚡ Tells Laravel to generate unique ULIDs right before saving records

    protected $table = 'media';

    // FIXED: Overriding model defaults so Eloquent treats the primary key as a non-incrementing string
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', // ⚡ Added to allow mass-assignment of the generated key
        'type', 'file_path', 'file_name', 'mime_type', 
        'file_size', 'collection_name', 'sort_order',
        'mediable_type', 'mediable_id'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['url', 'embed_url'];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Return direct secure CDN/S3 URL links for files, or raw paths for links
     */
    public function getUrlAttribute(): string
    {
        if ($this->type === 'video_link') {
            return $this->file_path;
        }

        // Generates a temporary link dynamically using your AWS credentials safely
        return Storage::disk('s3')->temporaryUrl(
            $this->file_path, 
            now()->addMinutes(20)
        );
    }

    /**
     * Compute native embed URL formats for video links
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->type !== 'video_link') {
            return null;
        }

        if (preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $this->file_path, $match)) {
            return "https://www.youtube.com/embed/" . $match[1];
        }

        if (preg_match('%vimeo\.com/([0-9]{1,10})%i', $this->file_path, $match)) {
            return "https://player.vimeo.com/video/" . $match[1];
        }

        return $this->file_path;
    }
}