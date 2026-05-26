<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
   

    public const CACHE_KEY = 'geo:languages:all';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'languages';

    protected $fillable = [
        'id',
        'code',
        'name',
        'native_name',
        'is_rtl',
    ];

    protected $casts = [
        'is_rtl' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
       
    }

    public static function flushCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Scope to find language by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}