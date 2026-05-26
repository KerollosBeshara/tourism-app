<?php

namespace Modules\Geo\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Geo\Models\Destination;
use Illuminate\Support\Facades\Auth;

class GetFilteredDestinationsAction
{
    public function execute(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Destination::query();
        // Enforce strict multi-tenant boundary checks via Auth user session
        $user = Auth::user();
        if ($user && isset($user->agency_id)) {
            $query->where('agency_id', $user->agency_id);
        }

        // Filter by country context if requested
        if (!empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        // Apply text and localization filters
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            
            $query->where(function ($q) use ($searchTerm) {
                $q->where('slug', 'like', $searchTerm)
                  // 💡 Points directly to your migration's jsonb layout column keys
                  ->orWhere('title_translations->en', 'like', $searchTerm);
            });
        }

        return $query->orderBy('slug', 'asc')->paginate($perPage);
    }
}