<?php

namespace Modules\Geo\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Geo\Models\Destination;
use Modules\Geo\Models\DestinationMedia;
use Modules\Geo\Jobs\UploadDestinationMediaJob;

class UploadDestinationMediaAction
{
    /**
     * Queue a destination media file upload for async processing.
     */
    public function execute(
        Destination $destination, 
        UploadedFile $file, 
        string $type = 'image',
        bool $isFeatured = false,
        ?int $sortOrder = null,
        ?array $captionTranslations = null,
        string $queue = 'images'
    ): DestinationMedia {
        // Create placeholder record in DB for immediate frontend UI feedback
        $media = DestinationMedia::create([
            'id'                   => (string) Str::ulid(),
            'destination_id'       => $destination->id,
            'type'                 => $type,
            'url'                  => 'pending-processing', // Placeholder until S3 path is generated
            'filename'             => $file->getClientOriginalName(),
            'mime_type'            => $file->getMimeType(),
            'file_size'            => 0,
            'disk'                 => 's3',
            'is_featured'          => $isFeatured,
            'sort_order'           => $sortOrder ?? 0,
            'caption_translations' => $captionTranslations,
        ]);

        // Save file to local temp disk securely for async worker access
        $filePath = $file->storeAs(
            'temp',
            uniqid('destination_') . '_' . $file->getClientOriginalName(),
            'local'
        );

        // Dispatch background processing job
        dispatch(new UploadDestinationMediaJob(
            $destination->id,
            $filePath,
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $type,
            $isFeatured,
            $sortOrder
        ))->onQueue($queue);

        return $media;
    }

    /**
     * Batch upload multiple media files asynchronously.
     */
    public function uploadBatch(
        Destination $destination, 
        array $files, 
        string $type = 'image',
        bool $markFirstAsFeatured = true,
        string $queue = 'images'
    ): array {
        $mediaRecords = [];

        foreach ($files as $index => $file) {
            $isFeatured = $markFirstAsFeatured && $index === 0;
            $mediaRecords[] = $this->execute(
                $destination,
                $file,
                $type,
                $isFeatured,
                $index,
                null,
                $queue
            );
        }

        return $mediaRecords;
    }
}