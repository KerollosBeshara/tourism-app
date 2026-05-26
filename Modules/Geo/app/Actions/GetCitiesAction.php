<?php

namespace Modules\Geo\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Geo\Models\City;

class GetCitiesAction
{
    /**
     * Execute the retrieval of cities with filters and pagination.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        return City::query()
            // Filter by name inside the array of jsonb objects
            ->when(!empty($filters['name']), function ($query) use ($filters) {
                $searchTerm = '%' . mb_strtolower($filters['name']) . '%';
                
                $query->whereRaw(
                    "EXISTS (
                        SELECT 1 
                        FROM jsonb_array_elements(name_translations) AS elem 
                        WHERE LOWER(elem->>'value') LIKE ?
                    )",
                    [$searchTerm]
                );
            })
            // Filter by Country ID
            ->when(!empty($filters['country_id']), function ($query) use ($filters) {
                $query->where('country_id', $filters['country_id']);
            })
            // Order by latest created by default
            ->latest('id')
            ->paginate($perPage);
    }
}