<?php

namespace Modules\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Geo\Models\Destination;

class SyncDestinationMediaAction
{
    /**
     * Synchronize a destination's photo/video gallery array.
     */
    public function execute(string $destinationId, array $mediaGallery): array
    {
        return DB::transaction(function () use ($destinationId, $mediaGallery) {
            $destination = Destination::findOrFail($destinationId);
            $keptIds = [];

            // Reset featured flags if a new featured cover item is provided
            if (collect($mediaGallery)->where('is_featured', true)->isNotEmpty()) {
                $destination->media()->update(['is_featured' => false]);
            }

            foreach ($mediaGallery as $media) {
                $upsertedMedia = $destination->media()->updateOrCreate(
                    ['id' => $media['id'] ?? null],
                    [
                        'type'                 => $media['type'],
                        'url'                  => $media['url'],
                        'caption_translations' => $media['caption_translations'] ?? null,
                        'sort_order'           => $media['sort_order'] ?? 0,
                        'is_featured'          => $media['is_featured'] ?? false,
                    ]
                );

                $keptIds[] = $upsertedMedia->id;
            }

            // Sync: Purge media items removed from the frontend image layout container
            $destination->media()->whereNotIn('id', $keptIds)->delete();

            return $keptIds;
        });
    }
}