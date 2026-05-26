<?php

namespace Modules\Geo\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
// use Modules\Geo\Services\DestinationCacheService;

class InvalidateDestinationCacheJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 30;
    public int $backoff = 5;

    public function __construct(public string $destinationId) {}

    public function handle(): void
    {
        try {
            // Add your cache flush pattern here:
            // $cacheService->forget($this->destinationId);

            \Log::debug('Destination cache tags flushed.', [
                'destination_id' => $this->destinationId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to invalidate Geo cache layers', [
                'destination_id' => $this->destinationId,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}