<?php

namespace Modules\DayTour\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Attributes\DeleteWhenMissing;
use Modules\Core\Services\ImageService;
use Modules\DayTour\Models\DayTour;
use Modules\DayTour\Models\DayTourImage;

#[DeleteWhenMissing]
class UploadDayTourImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;
    public int $backoff = 120;

    /**
     * Store the uploaded file in storage to avoid serialization issues
     */
    private string $filePath;

    public function __construct(
        public string $dayTourId,
        UploadedFile $file,
        public bool $isPrimary = false,
        public ?int $sortOrder = null
    ) {
        // Store file temporarily
        $this->filePath = $file->storeAs(
            'temp',
            $file->getClientOriginalName(),
            'local'
        );
    }

    /**
     * Execute the job
     */
    public function handle(ImageService $imageService): void
    {
        try {
            // Get the day tour
            $dayTour = DayTour::findOrFail($this->dayTourId);

            // Retrieve the stored file
            $storagePath = \Storage::disk('local')->path($this->filePath);
            if (!file_exists($storagePath)) {
                throw new \Exception('Temporary file not found');
            }

            // Create UploadedFile from stored path
            $file = new UploadedFile(
                $storagePath,
                basename($this->filePath),
                mime_content_type($storagePath),
                null,
                true
            );

            // Process and upload image using Core ImageService
            $imageData = $imageService->uploadAndOptimize(
                'day-tours',
                $this->dayTourId,
                $file,
                1200,
                800,
                true // Create variants
            );

            // Create database record
            $image = DayTourImage::create([
                'day_tour_id' => $this->dayTourId,
                's3_path' => $imageData['original']['s3_path'],
                'filename' => $imageData['original']['filename'],
                'mime_type' => $imageData['original']['mime_type'],
                'file_size' => $imageData['original']['file_size'],
                'disk' => $imageData['original']['disk'],
                'is_primary' => $this->isPrimary,
                'sort_order' => $this->sortOrder ?? 0,
            ]);

            // Store thumbnail and medium URLs in meta if needed
            if (isset($imageData['thumbnail'])) {
                $image->update([
                    'meta' => [
                        'thumbnail_url' => $imageData['thumbnail']['s3_path'],
                        'medium_url' => $imageData['medium']['s3_path'],
                    ],
                ]);
            }

            // If marked as primary, unmark others
            if ($this->isPrimary) {
                DayTourImage::where('day_tour_id', $this->dayTourId)
                    ->where('id', '!=', $image->id)
                    ->update(['is_primary' => false]);
            }

            // Dispatch job to update cache
            dispatch(new InvalidateDayTourCacheJob($this->dayTourId))->onQueue('cache');

            \Log::info('DayTour image uploaded successfully', [
                'image_id' => $image->id,
                'day_tour_id' => $this->dayTourId,
                's3_path' => $imageData['original']['s3_path'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to upload DayTour image', [
                'day_tour_id' => $this->dayTourId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            // Clean up temporary file
            if (\Storage::disk('local')->exists($this->filePath)) {
                \Storage::disk('local')->delete($this->filePath);
            }
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('UploadDayTourImageJob failed', [
            'day_tour_id' => $this->dayTourId,
            'exception' => $exception->getMessage(),
        ]);

        // Clean up on failure
        if (\Storage::disk('local')->exists($this->filePath)) {
            \Storage::disk('local')->delete($this->filePath);
        }
    }
}
