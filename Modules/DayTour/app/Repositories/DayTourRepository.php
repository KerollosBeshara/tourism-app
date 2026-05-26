<?php

namespace Modules\DayTour\Repositories;

use Modules\DayTour\Models\DayTour;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DayTourRepository
{
    /**
     * Get day tour by ID with relations
     */
    public function findById(string $id): ?DayTour
    {
        return DayTour::with('images', 'city', 'destination')->find($id);
    }

    /**
     * Get all active day tours
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return DayTour::active()
            ->with('images', 'city', 'destination')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get day tours by agency
     */
    public function getByAgency(string $agencyId, int $perPage = 15): LengthAwarePaginator
    {
        return DayTour::byAgency($agencyId)
            ->active()
            ->with('images', 'city', 'destination')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get day tours by city
     */
    public function getByCity(int $cityId, int $perPage = 15): LengthAwarePaginator
    {
        return DayTour::byCity($cityId)
            ->active()
            ->with('images', 'city', 'destination')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get day tours by destination
     */
    public function getByDestination(int $destinationId, int $perPage = 15): LengthAwarePaginator
    {
        return DayTour::byDestination($destinationId)
            ->active()
            ->with('images', 'city', 'destination')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get shared day tours
     */
    public function getShared(int $perPage = 15): LengthAwarePaginator
    {
        return DayTour::shared()
            ->active()
            ->with('images', 'city', 'destination')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get recent day tours
     */
    public function getRecent(int $days = 30, int $perPage = 15): LengthAwarePaginator
    {
        return DayTour::recent($days)
            ->active()
            ->with('images', 'city', 'destination')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Search day tours
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = DayTour::query()
            ->with('images', 'city', 'destination')
            ->active();

        if (!empty($filters['agency_id'])) {
            $query->byAgency($filters['agency_id']);
        }

        if (!empty($filters['city_id'])) {
            $query->byCity($filters['city_id']);
        }

        if (!empty($filters['destination_id'])) {
            $query->byDestination($filters['destination_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->whereRaw("title_translations::text ILIKE ?", [$search])
                ->orWhereRaw("description_translations::text ILIKE ?", [$search]);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get total count
     */
    public function count(array $filters = []): int
    {
        $query = DayTour::query();

        if (!empty($filters['is_active'])) {
            $query->where('is_active', true);
        }

        if (!empty($filters['agency_id'])) {
            $query->where('agency_id', $filters['agency_id']);
        }

        return $query->count();
    }

    /**
     * Delete day tour and related images
     */
    public function delete(DayTour $dayTour): bool
    {
        // Images are cascade deleted via foreign key
        return $dayTour->delete();
    }

    /**
     * Restore soft-deleted day tour
     */
    public function restore(string $dayTourId): bool
    {
        return (bool) DayTour::withTrashed()
            ->find($dayTourId)
            ?->restore();
    }

    /**
     * Force delete day tour (hard delete)
     */
    public function forceDelete(string $dayTourId): bool
    {
        return (bool) DayTour::withTrashed()
            ->find($dayTourId)
            ?->forceDelete();
    }
}
