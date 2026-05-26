<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Modules\Media\Traits\InteractsWithMedia; // 1. Added missing import

class Destination extends Model
{
    use SoftDeletes, HasUlids, InteractsWithMedia; // Fixed spacing

    protected $table = 'destinations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'agency_id',
        'country_id',
        'slug',
        'title_translations',
        'description_translations',
        'latitude',
        'longitude',
        'map_data',
        'geojson',
        'regional_data',
        'country_code',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'title_translations'       => 'array',
        'description_translations' => 'array',
        'latitude'                 => 'float',
        'longitude'                => 'float',
        'map_data'                 => 'array',
        'geojson'                  => 'array',
        'regional_data'            => 'array',
        'view_count'               => 'integer',
        'is_active'                => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(\Modules\Agency\Models\Agency::class, 'agency_id');
    }

    public function tourismItems(): HasMany
    {
        return $this->hasMany(DestinationTourismItem::class, 'destination_id')->orderBy('sort_order', 'asc');
    }

    /**
     * 2. Renamed from media() to images() to avoid overriding the InteractsWithMedia trait.
     * This allows $destination->media to return EVERYTHING (videos, banners, images), 
     * while $destination->images() returns only the gallery images.
     */
    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Modules\Media\Models\Media::class, 'mediable')
                    ->where('type', 'image')
                    ->where('collection_name', 'gallery') // Changed from 'image' to 'destination'
                    ->orderBy('sort_order', 'asc');
    }

    /**
     * Get the main featured image for this destination.
     */
    public function featuredMedia(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(\Modules\Media\Models\Media::class, 'mediable')
                   ->where('collection_name', 'banner'); // 3. Fixed spelling from 'bannar' to 'banner'
    }


   public function videos(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Modules\Media\Models\Media::class, 'mediable')
                    ->where('type', 'video_link')
                    ->orderBy('sort_order', 'asc'); // Keep order consistent
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id', 'country_id');
    }

    public function dayTours(): HasMany
    {
        return $this->hasMany(\Modules\DayTour\Models\DayTour::class, 'destination_id');
    }

    /**
     * Get destination title in specific locale
     */
    public function getTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        if (!is_array($this->title_translations)) return null;

        foreach ($this->title_translations as $translation) {
            if ($translation['locale'] === $locale) return $translation['value'] ?? null;
        }
        return $this->title_translations[0]['value'] ?? null;
    }

    /**
     * Get destination description in specific locale
     */
    public function getDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        if (!is_array($this->description_translations)) return null;

        foreach ($this->description_translations as $translation) {
            if ($translation['locale'] === $locale) return $translation['value'] ?? null;
        }
        return $this->description_translations[0]['value'] ?? null;
    }

    /**
     * Scope to get active destinations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('deleted_at');
    }

    /**
     * Scope by country
     */
    public function scopeByCountry($query, string $countryId)
    {
        return $query->where('country_id', $countryId);
    }
}