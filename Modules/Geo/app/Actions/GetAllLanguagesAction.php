<?php

namespace Modules\Geo\Actions;

use Illuminate\Support\Facades\Cache;
use Modules\Geo\Models\Language;
use Illuminate\Support\Collection;

class GetAllLanguagesAction
{
    private const CACHE_TTL = 3600; // 1 hour

    public function execute(): Collection
    {
        $cached = Cache::get(Language::CACHE_KEY);

        if (is_object($cached) && get_class($cached) === '__PHP_Incomplete_Class') {
            Cache::forget(Language::CACHE_KEY);
            $cached = null;
        }

        $languages = $cached ?: Cache::remember(Language::CACHE_KEY, self::CACHE_TTL, function () {
            return Language::select(['id', 'name'])
                ->orderBy('name')
                ->get()
                ->toArray();
        });

        return Language::hydrate($languages);
    }

    public function flushCache(): void
    {
        Cache::forget(Language::CACHE_KEY);
    }
}