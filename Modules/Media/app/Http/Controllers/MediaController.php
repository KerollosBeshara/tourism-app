<?php

namespace Modules\Media\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\Relation; // ⚡ FIXED: Added missing import
use Modules\Media\Http\Requests\StoreMediaRequest;
use Modules\Media\Models\Media;
use Modules\Media\Jobs\OptimizeAndUploadImage;
use App\Services\ImageService; // ⚡ FIXED: Added for destroy method

class MediaController extends Controller
{
   public function sync(StoreMediaRequest $request, ImageService $imageService): JsonResponse
    {
        $validated = $request->validated();
        $modelClass = Relation::getMorphedModel($validated['mediable_type']) ?? $validated['mediable_type'];
        $parentModel = $modelClass::findOrFail($validated['mediable_id']);

        $itemsData = $request->input('items', []);

        // 1. DELETE orphaned media (same as before)
        $incomingIds = collect($itemsData)->pluck('id')->filter()->toArray();
        $parentModel->media()
            ->whereNotIn('id', $incomingIds)
            ->get()
            ->each(function ($media) use ($imageService) {
                if ($media->type !== 'video_link' && $media->file_path) {
                    $imageService->deleteFromS3($media->file_path);
                }
                $media->delete();
            });

        // 2. PROCESS incoming items
        $processedRecords = [];
        foreach ($itemsData as $index => $item) {
            $collection = $item['collection_name'] ?? 'gallery';
            $mediaId = $item['id'] ?? null;

            // Extract the file specifically for this index from the request
            // Laravel allows accessing nested files using dot notation
            $file = $request->file("items.{$index}.file");

            if ($mediaId) {
                $media = $parentModel->media()->findOrFail($mediaId);
                $media->update(['sort_order' => $index, 'collection_name' => $collection]);
                $processedRecords[] = $media;
            } else {
                $processedRecords[] = $this->createNewMedia($parentModel, $item, $file, $index);
            }
        }

        return response()->json(['message' => 'Sync successful', 'data' => $processedRecords], 200);
    }


    protected function createNewMedia($parentModel, $item, $file, $index)
    {
        $media = new Media();
        $media->collection_name = $item['collection_name'] ?? 'gallery';
        $media->type = $item['type'] ?? 'image';
        $media->sort_order = $index;
        $media->file_name = $file ? $file->getClientOriginalName() : ($item['video_url'] ?? 'link');

        if ($media->type === 'video_link') {
            $media->file_path = $item['video_url'] ?? 'external_link';
            $parentModel->media()->save($media);
        } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
            // 1. Set a placeholder path
            $media->file_path = 'pending'; 
            $parentModel->media()->save($media);

            // 2. Store locally for the background worker to pick up
            // We use storeAs to keep the filename clean
            $tempPath = $file->store('temp_uploads', 'local');
            
            // 3. Dispatch the job with the required 3 arguments
            // Media: $media
            // Temp Path: $tempPath
            // Target Directory: 'media' (or your preferred folder in S3)
            OptimizeAndUploadImage::dispatch($media, $tempPath, 'media');
        }

        return $media;
    }
}