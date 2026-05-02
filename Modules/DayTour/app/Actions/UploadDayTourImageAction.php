<?php

namespace Modules\DayTour\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\DayTour\Models\DayTour;
use Modules\DayTour\Models\DayTourImage;
use Modules\DayTour\Jobs\UploadDayTourImageJob;

class UploadDayTourImageAction
{
    /**
     * Queue image upload for async processing
     * 
     * Dispatches job instead of processing synchronously for better performance
     */
    public function execute(
        DayTour $dayTour, 
        UploadedFile $file, 
        bool $isPrimary = false,
        ?int $sortOrder = null,
        string $queue = 'images'
    ): DayTourImage {
        // Create placeholder record in DB for immediate feedback
        $image = DayTourImage::create([
            'id' => (string) Str::ulid(),
            'day_tour_id' => $dayTour->id,
            's3_path' => 'pending-processing',
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => 0,
            'disk' => 's3',
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder ?? 0,
        ]);

        // Dispatch upload job to queue for async processing
        dispatch(new UploadDayTourImageJob(
            $dayTour->id,
            $file,
            $isPrimary,
            $sortOrder
        ))->onQueue($queue);

        return $image;
    }

    /**
     * Batch upload images asynchronously
     */
    public function uploadBatch(
        DayTour $dayTour, 
        array $files, 
        bool $markFirstAsPrimary = true,
        string $queue = 'images'
    ): array {
        $images = [];

        foreach ($files as $index => $file) {
            $isPrimary = $markFirstAsPrimary && $index === 0;
            $image = $this->execute(
                $dayTour,
                $file,
                $isPrimary,
                $index,
                $queue
            );
            $images[] = $image;
        }

        return $images;
    }

    /**
     * Upload synchronously (for critical operations)
     * Use with caution - blocks the request
     */
    public function executeSync(
        DayTour $dayTour,
        UploadedFile $file,
        bool $isPrimary = false,
        ?int $sortOrder = null
    ): DayTourImage {
        $job = new UploadDayTourImageJob(
            $dayTour->id,
            $file,
            $isPrimary,
            $sortOrder
        );

        // Execute synchronously
        $job->handle(app(\Modules\Core\Services\ImageService::class));

        return DayTourImage::where('day_tour_id', $dayTour->id)
            ->orderByDesc('created_at')
            ->first();
    }
}
