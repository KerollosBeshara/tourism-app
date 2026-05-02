<?php

namespace Modules\DayTour\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Attributes\DeleteWhenMissing;
use Modules\Core\Services\ImageService;
use Modules\DayTour\Models\DayTour;
use Modules\DayTour\Models\DayTourImage;

#[DeleteWhenMissing]
class ProcessDayTourImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 60;

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
            // Get metadata about the image
            $metadata = $imageService->getImageMetadata($this->image->s3_path);

            if ($metadata) {
                $this->image->update([
                    'file_size' => $metadata['size'] ?? 0,
                ]);
            }

            // Log successful processing
            \Log::info('Image processed successfully', [
                'image_id' => $this->image->id,
                'day_tour_id' => $this->image->day_tour_id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to process image', [
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
        \Log::error('ProcessDayTourImageJob failed', [
            'image_id' => $this->image->id,
            'exception' => $exception->getMessage(),
        ]);

        // Mark image as failed (optional)
        // $this->image->update(['processing_failed' => true]);
    }
}
