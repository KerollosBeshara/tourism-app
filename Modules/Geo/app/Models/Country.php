<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'countries';

    protected $fillable = [
        'iso_code',
        'emoji_flag',
        'name_translations',
    ];

    protected $casts = [
        'name_translations' => 'array',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Country $country) {
            // Automatically generate the Primary Key from the incoming ISO Code
            $country->id = strtoupper(trim($country->iso_code));
        });
    }

    /**
     * Scope to find country by ISO code
     */
    public function scopeByIsoCode($query, string $isoCode)
    {
        return $query->where('iso_code', strtoupper($isoCode));
    }

    /**
     * Get country name for specific locale
     */
    public function getNameByLocale(string $locale = 'en'): ?string
    {
        if (!is_array($this->name_translations)) {
            return null;
        }

        foreach ($this->name_translations as $translation) {
            if (isset($translation['locale']) && $translation['locale'] === $locale) {
                return $translation['value'];
            }
        }

        return null;
    }
}