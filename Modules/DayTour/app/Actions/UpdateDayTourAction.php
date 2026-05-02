<?php

namespace Modules\DayTour\Actions;

use Modules\DayTour\Models\DayTour;
use Modules\DayTour\Services\DayTourCacheService;

class UpdateDayTourAction
{
    public function __construct(private DayTourCacheService $cacheService)
    {
    }

    /**
     * Update an existing day tour
     */
    public function execute(DayTour $dayTour, array $data): DayTour
    {
        $updateData = [];

        if (isset($data['city_id'])) {
            $updateData['city_id'] = $data['city_id'];
        }

        if (isset($data['destination_id'])) {
            $updateData['destination_id'] = $data['destination_id'];
        }

        if (isset($data['title_translations'])) {
            $updateData['title_translations'] = $data['title_translations'];
        }

        if (isset($data['description_translations'])) {
            $updateData['description_translations'] = $data['description_translations'];
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        if (isset($data['is_shared'])) {
            $updateData['is_shared'] = $data['is_shared'];
        }

        $dayTour->update($updateData);

        // Refresh cache
        $this->cacheService->forget($dayTour->id);
        $this->cacheService->cache($dayTour);

        return $dayTour;
    }
}
