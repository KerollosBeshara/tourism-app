<?php

namespace Modules\DayTour\Actions;

use Illuminate\Support\Str;
use Modules\DayTour\Models\DayTour;
use Modules\DayTour\Services\DayTourCacheService;

class CreateDayTourAction
{
    public function __construct(private DayTourCacheService $cacheService)
    {
    }

    /**
     * Create a new day tour
     */
    public function execute(array $data): DayTour
    {
        $dayTour = DayTour::create([
            'id' => (string) Str::ulid(),
            'agency_id' => $data['agency_id'],
            'city_id' => $data['city_id'],
            'destination_id' => $data['destination_id'],
            'title_translations' => $data['title_translations'],
            'description_translations' => $data['description_translations'],
            'is_active' => $data['is_active'] ?? true,
            'is_shared' => $data['is_shared'] ?? false,
        ]);

        // Cache the new day tour
        $this->cacheService->cache($dayTour);

        return $dayTour;
    }
}
