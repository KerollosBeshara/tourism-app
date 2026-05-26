<?php

namespace Modules\DayTour\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Attributes\DeleteWhenMissing;
use Modules\DayTour\Models\DayTourImage;
use App\Services\ImageService;

#[DeleteWhenMissing]
class DeleteDayTourImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        #[WithoutRelations]
        public DayTourImage $image
    ) {}

    /**
     * Execute the job
     */
    public function handle(ImageService $imageService): void
    {
        try {
            $dayTourId = $this->image->day_tour_id;
            $s3Path = $this->image->s3_path;

            // Delete from S3 (including variants)
            $imageService->deleteFromS3($s3Path);

            // Delete from database
            $this->image->delete();

            // Invalidate cache
            dispatch(new InvalidateDayTourCacheJob($dayTourId))->onQueue('cache');

            \Log::info('DayTour image deleted successfully', [
                'image_id' => $this->image->id,
                'day_tour_id' => $dayTourId,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to delete DayTour image', [
                'image_id' => $this->image->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('DeleteDayTourImageJob failed', [
            'image_id' => $this->image->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
