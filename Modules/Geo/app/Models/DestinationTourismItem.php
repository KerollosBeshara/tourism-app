<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class DestinationTourismItem extends Model
{
    use HasUlids;

    protected $table = 'destination_tourism_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'destination_id',
        'sort_order',
        'icon',
        'title_translations',
        'description_translations',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'title_translations' => 'array',
        'description_translations' => 'array',
    ];

    /**
     * Parent destination connection
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    /**
     * Translation getter for Title
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
     * Translation getter for Description
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
}