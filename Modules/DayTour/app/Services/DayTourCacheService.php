<?php

namespace Modules\DayTour\Services;

use Illuminate\Support\Facades\Cache;
use Modules\DayTour\Models\DayTour;

class DayTourCacheService
{
    private const CACHE_PREFIX = 'day_tour';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cache key for day tour
     */
    private function getCacheKey(string $dayTourId): string
    {
        return self::CACHE_PREFIX . ':' . $dayTourId;
    }

    /**
     * Get cached day tour with images
     */
    public function get(string $dayTourId): ?DayTour
    {
        return Cache::remember(
            $this->getCacheKey($dayTourId),
            self::CACHE_TTL,
            fn() => DayTour::with('images')->find($dayTourId)
        );
    }

    /**
     * Cache day tour data
     */
    public function cache(DayTour $dayTour): void
    {
        Cache::put(
            $this->getCacheKey($dayTour->id),
            $dayTour->load('images'),
            self::CACHE_TTL
        );
    }

    /**
     * Invalidate cache for day tour
     */
    public function forget(string $dayTourId): bool
    {
        return Cache::forget($this->getCacheKey($dayTourId));
    }

    /**
     * Invalidate all day tour caches
     */
    public function forgetAll(): bool
    {
        // Use tags if available
        if (Cache::supportsTags()) {
            Cache::tags(self::CACHE_PREFIX)->flush();
            return true;
        }

        return true;
    }

    /**
     * Cache agency day tours list
     */
    public function getAgencyDayTours(string $agencyId, int $page = 1): array
    {
        $cacheKey = self::CACHE_PREFIX . ':agency:' . $agencyId . ':page:' . $page;

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => DayTour::where('agency_id', $agencyId)
                ->with('images', 'city', 'destination')
                ->active()
                ->paginate(15)
                ->toArray()
        );
    }

    /**
     * Cache city day tours
     */
    public function getCityDayTours(int $cityId, int $page = 1): array
    {
        $cacheKey = self::CACHE_PREFIX . ':city:' . $cityId . ':page:' . $page;

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => DayTour::where('city_id', $cityId)
                ->with('images', 'destination')
                ->active()
                ->paginate(15)
                ->toArray()
        );
    }

    /**
     * Cache search results
     */
    public function getSearchResults(array $filters, int $page = 1): array
    {
        $cacheKey = self::CACHE_PREFIX . ':search:' . md5(json_encode($filters)) . ':page:' . $page;

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => $this->buildSearchQuery($filters)
                ->with('images', 'city', 'destination')
                ->active()
                ->paginate(15)
                ->toArray()
        );
    }

    /**
     * Build search query
     */
    private function buildSearchQuery($filters)
    {
        $query = DayTour::query();

        if (!empty($filters['agency_id'])) {
            $query->where('agency_id', $filters['agency_id']);
        }

        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['destination_id'])) {
            $query->where('destination_id', $filters['destination_id']);
        }

        return $query;
    }
}
