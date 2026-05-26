<?php

namespace Modules\DayTour\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\Attributes\DeleteWhenMissing;
use App\Services\ImageService;
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
     * Store the file path and metadata
     */
    private string $filePath;
    private string $originalFileName;
    private string $mimeType;

    public function __construct(
        public string $dayTourId,
        string $filePath,
        string $originalFileName,
        string $mimeType,
        public bool $isPrimary = false,
        public ?int $sortOrder = null
    ) {
        $this->filePath = $filePath;
        $this->originalFileName = $originalFileName;
        $this->mimeType = $mimeType;
    }

    /**
     * Execute the job
     */
    public function handle(ImageService $imageService): void
    {
        try {
            // Get the day tour
            $dayTour = DayTour::findOrFail($this->dayTourId);

            // Read file from local disk
            // $fullPath = \Storage::disk('local')->path($this->filePath);
            $fullPath = storage_path('app/private/' . $this->filePath);

            \Log::info("Worker checking file:", [
                'path' => $fullPath,
                'exists' => file_exists($fullPath),
                'is_readable' => is_readable($fullPath),
                'current_user' => posix_getpwuid(posix_geteuid())['name'] ?? 'unknown'
            ]);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Temp file not found at: {$this->filePath}");
            }

            try {
                // Create UploadedFile from disk path
                $file = new UploadedFile(
                    $fullPath,
                    $this->originalFileName,
                    $this->mimeType,
                    null,
                    true
                );

                // Process and upload image to S3
                $imageData = $imageService->uploadAndOptimize(
                    'day-tours',
                    $this->dayTourId,
                    $file,
                    1200,
                    800,
                    true // Create variants
                );

                // Update placeholder record with real S3 paths
                $image = DayTourImage::where('day_tour_id', $this->dayTourId)
                    ->where('filename', $this->originalFileName)
                    ->first();

                if ($image) {
                    $image->update([
                        's3_path' => $imageData['original']['s3_path'],
                        'mime_type' => $imageData['original']['mime_type'],
                        'file_size' => $imageData['original']['file_size'],
                        'meta' => [
                            'thumbnail_url' => $imageData['thumbnail']['s3_path'] ?? null,
                            'medium_url' => $imageData['medium']['s3_path'] ?? null,
                        ],
                    ]);
                }

                // If marked as primary, unmark others
                if ($this->isPrimary && $image) {
                    DayTourImage::where('day_tour_id', $this->dayTourId)
                        ->where('id', '!=', $image->id)
                        ->update(['is_primary' => false]);
                }

                // Dispatch cache invalidation
                dispatch(new InvalidateDayTourCacheJob($this->dayTourId))->onQueue('cache');

                \Log::info('DayTour image uploaded successfully', [
                    'image_id' => $image->id ?? 'unknown',
                    'day_tour_id' => $this->dayTourId,
                    's3_path' => $imageData['original']['s3_path'],
                ]);

            } finally {
                // Clean up temp file from disk
                // if (\Storage::disk('local')->exists($this->filePath)) {
                //     \Storage::disk('local')->delete($this->filePath);
                // }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to upload DayTour image', [
                'day_tour_id' => $this->dayTourId,
                'filename' => $this->originalFileName,
                'file_path' => $this->filePath,
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
        \Log::error('UploadDayTourImageJob failed', [
            'day_tour_id' => $this->dayTourId,
            'filename' => $this->originalFileName,
            'file_path' => $this->filePath,
            'exception' => $exception->getMessage(),
        ]);

        // Clean up temp file on failure
        // if (\Storage::disk('local')->exists($this->filePath)) {
        //     \Storage::disk('local')->delete($this->filePath);
        // }
    }
}

