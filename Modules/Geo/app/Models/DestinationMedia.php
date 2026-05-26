<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class DestinationMedia extends Model
{
    use HasUlids;

    protected $table = 'destination_media';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'destination_id',
        'type',
        'url',
        'caption_translations',
        'sort_order',
        'is_featured',
    ];

    protected $casts = [
        'caption_translations' => 'array',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
    ];

    /**
     * Parent destination connection
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    /**
     * Scope to fetch only raw image components
     */
    public function scopePhotos($query)
    {
        return $query->where('type', 'photo');
    }

    /**
     * Scope to fetch only external stream assets
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video_link');
    }

    /**
     * Translation getter for Caption
     */
    public function getCaption(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        if (!is_array($this->caption_translations)) return null;

        foreach ($this->caption_translations as $translation) {
            if ($translation['locale'] === $locale) return $translation['value'] ?? null;
        }
        return $this->caption_translations[0]['value'] ?? null;
    }
}