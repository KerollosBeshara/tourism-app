<?php

namespace Modules\Geo\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\Attributes\DeleteWhenMissing;
use App\Services\ImageService;
use Modules\Geo\Models\Destination;
use Modules\Geo\Models\DestinationMedia;

#[DeleteWhenMissing]
class UploadDestinationMediaJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;
    public int $backoff = 120;

    private string $filePath;
    private string $originalFileName;
    private string $mimeType;

    public function __construct(
        public string $destinationId,
        string $filePath,
        string $originalFileName,
        string $mimeType,
        public string $type = 'image',
        public bool $isFeatured = false,
        public ?int $sortOrder = null
    ) {
        $this->filePath = $filePath;
        $this->originalFileName = $originalFileName;
        $this->mimeType = $mimeType;
    }

    public function handle(ImageService $imageService): void
    {
        try {
            $destination = Destination::findOrFail($this->destinationId);
            $fullPath = storage_path('app/private/' . $this->filePath);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Temp file not found at: {$this->filePath}");
            }

            try {
                $file = new UploadedFile(
                    $fullPath,
                    $this->originalFileName,
                    $this->mimeType,
                    null,
                    true // Set test mode to true to bypass native upload validation hooks
                );

                // Use the exact same shared ImageService engine
                $imageData = $imageService->uploadAndOptimize(
                    'destinations',
                    $this->destinationId,
                    $file,
                    1600, // Destinations look better with wider landscape dimensions
                    1000,
                    true  // Create optimized thumbnail and medium layouts
                );

                $media = DestinationMedia::where('destination_id', $this->destinationId)
                    ->where('filename', $this->originalFileName)
                    ->first();

                if ($media) {
                    $media->update([
                        'url'       => $imageData['original']['s3_path'],
                        'mime_type' => $imageData['original']['mime_type'],
                        'file_size' => $imageData['original']['file_size'],
                        'meta'      => [
                            'thumbnail_url' => $imageData['thumbnail']['s3_path'] ?? null,
                            'medium_url'    => $imageData['medium']['s3_path'] ?? null,
                        ],
                    ]);
                }

                // If marked as the main featured asset, clear previous ones
                if ($this->isFeatured && $media) {
                    DestinationMedia::where('destination_id', $this->destinationId)
                        ->where('id', '!=', $media->id)
                        ->update(['is_featured' => false]);
                }

                // Invalidate Cache for this specific Destination
                dispatch(new InvalidateDestinationCacheJob($this->destinationId))->onQueue('cache');

                \Log::info('Destination media uploaded and optimized via S3.', [
                    'media_id'       => $media->id ?? 'unknown',
                    'destination_id' => $this->destinationId,
                    'url'            => $imageData['original']['s3_path'],
                ]);

            } finally {
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to execute Destination media upload process', [
                'destination_id' => $this->destinationId,
                'filename'       => $this->originalFileName,
                'error'          => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('UploadDestinationMediaJob completely failed.', [
            'destination_id' => $this->destinationId,
            'filename'       => $this->originalFileName,
            'exception'      => $exception->getMessage(),
        ]);

        $fullPath = storage_path('app/private/' . $this->filePath);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}