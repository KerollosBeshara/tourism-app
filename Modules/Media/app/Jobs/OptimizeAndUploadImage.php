<?php

namespace Modules\Media\Jobs;

use Modules\Media\Models\Media;
use App\Services\ImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Exception;
use Throwable;

class OptimizeAndUploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(
        protected Media $media,
        protected string $tempLocalPath,
        protected string $targetDirectory,
        protected ?int $width = 1400,
        protected ?int $height = 900
    ) {}

    public function handle(ImageService $imageService): void
    {
        $this->media->refresh();
        if (!$this->media->exists) {
            $this->cleanup();
            return;
        }

        // Process directly from local path
        $localPath = Storage::disk('local')->path($this->tempLocalPath);
        $uploadedFile = new \Illuminate\Http\UploadedFile($localPath, basename($localPath), null, null, true);

        $results = $imageService->uploadAndOptimize($this->targetDirectory, $uploadedFile, $this->width, $this->height, true);

        $this->media->update([
            'file_path' => $results['original']['s3_path'],
            'mime_type' => 'image/webp',
            'file_size' => $results['original']['file_size'],
        ]);

        $this->cleanup();
    }

    private function cleanup()
    {
        if (Storage::disk('local')->exists($this->tempLocalPath)) {
            Storage::disk('local')->delete($this->tempLocalPath);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->cleanup();
        if ($this->media->exists) {
            $this->media->update(['file_path' => 'error_processing']);
        }
    }
}