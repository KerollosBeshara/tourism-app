<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'currencies';

    protected $fillable = [
        'id',
        'code',
        'symbol',
        'name_translations',
        'decimal_places',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'decimal_places' => 'integer',
    ];

    /**
     * Scope to find currency by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Get currency name for specific locale
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

    /**
     * Format amount with currency symbol
     */
    public function format($amount): string
    {
        return $this->symbol . number_format($amount, $this->decimal_places);
    }
}
