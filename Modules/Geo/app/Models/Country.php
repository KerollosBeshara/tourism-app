<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'countries';

    protected $fillable = [
        'id',
        'iso_code',
        'emoji_flag',
        'name_translations',
    ];

    protected $casts = [
        'name_translations' => 'array',
    ];

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
            if ($translation['locale'] === $locale) {
                return $translation['value'];
            }
        }

        return null;
    }
}
