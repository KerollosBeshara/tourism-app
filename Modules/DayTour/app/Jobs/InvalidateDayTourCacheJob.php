<?php

namespace Modules\DayTour\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\DayTour\Services\DayTourCacheService;

class InvalidateDayTourCacheJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 30;
    public int $backoff = 5;

    public function __construct(public string $dayTourId) {}

    /**
     * Execute the job
     */
    public function handle(DayTourCacheService $cacheService): void
    {
        try {
            $cacheService->forget($this->dayTourId);

            \Log::debug('DayTour cache invalidated', [
                'day_tour_id' => $this->dayTourId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to invalidate cache', [
                'day_tour_id' => $this->dayTourId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
