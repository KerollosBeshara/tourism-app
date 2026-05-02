<?php

namespace Modules\DayTour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class DayTour extends Model
{
    use SoftDeletes;

    protected $table = 'day_tours';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'agency_id',
        'city_id',
        'destination_id',
        'title_translations',
        'description_translations',
        'is_active',
        'is_shared',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'title',
        'description',
        'primary_image',
    ];

    /**
     * Get the city that owns this day tour
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(\Modules\Geo\Models\City::class, 'city_id');
    }

    /**
     * Get the destination that owns this day tour
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(\Modules\Master\Models\Destination::class, 'destination_id');
    }

    /**
     * Get all images for this day tour
     */
    public function images(): HasMany
    {
        return $this->hasMany(DayTourImage::class, 'day_tour_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    /**
     * Get the primary image for this day tour
     */
    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true)->limit(1);
    }

    /**
     * Get title in specific locale
     */
    public function getTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        
        if (!is_array($this->title_translations)) {
            return null;
        }

        foreach ($this->title_translations as $translation) {
            if ($translation['locale'] === $locale) {
                return $translation['value'] ?? null;
            }
        }

        return $this->title_translations[0]['value'] ?? null;
    }

    /**
     * Get description in specific locale
     */
    public function getDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        
        if (!is_array($this->description_translations)) {
            return null;
        }

        foreach ($this->description_translations as $translation) {
            if ($translation['locale'] === $locale) {
                return $translation['value'] ?? null;
            }
        }

        return $this->description_translations[0]['value'] ?? null;
    }

    /**
     * Append title in current locale (for appended attribute)
     */
    protected function title(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getTitle() ?? 'N/A'
        );
    }

    /**
     * Append description in current locale (for appended attribute)
     */
    protected function description(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getDescription() ?? 'N/A'
        );
    }

    /**
     * Append primary image (for appended attribute)
     */
    protected function primaryImage(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->images->firstWhere('is_primary', true) 
                ?? $this->images->first()
        );
    }

    /**
     * Scope for active day tours
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for shared day tours
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope by city
     */
    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope by destination
     */
    public function scopeByDestination($query, int $destinationId)
    {
        return $query->where('destination_id', $destinationId);
    }

    /**
     * Scope by agency
     */
    public function scopeByAgency($query, string $agencyId)
    {
        return $query->where('agency_id', $agencyId);
    }

    /**
     * Scope for recent day tours
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
