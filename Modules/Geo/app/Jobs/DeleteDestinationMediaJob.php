<?php

namespace Modules\Geo\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Attributes\DeleteWhenMissing;
use Modules\Geo\Models\DestinationMedia;
use App\Services\ImageService;

#[DeleteWhenMissing]
class DeleteDestinationMediaJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        #[WithoutRelations]
        public DestinationMedia $media
    ) {}

    public function handle(ImageService $imageService): void
    {
        try {
            $destinationId = $this->media->destination_id;
            $s3Path = $this->media->url; // Maps directly to your storage path string

            // Delete root image + layout variations via S3 Client wrapper
            if ($s3Path && $s3Path !== 'pending-processing') {
                $imageService->deleteFromS3($s3Path);
            }

            $this->media->delete();

            // Clear Geo module query cache layers
            dispatch(new InvalidateDestinationCacheJob($destinationId))->onQueue('cache');

            \Log::info('Destination asset purged from storage and database tables.', [
                'media_id'       => $this->media->id,
                'destination_id' => $destinationId,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to purge destination asset cleanly', [
                'media_id' => $this->media->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('DeleteDestinationMediaJob failed', [
            'media_id'  => $this->media->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}