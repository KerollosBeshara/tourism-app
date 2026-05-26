<?php

namespace Modules\Geo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Geo\Actions\SyncDestinationMediaAction;
use Modules\Geo\Http\Requests\DestinationMediaRequest;
use Modules\Geo\Http\Resources\DestinationMediaResource;
use Modules\Geo\Models\Destination;

class DestinationMediaController extends Controller
{
    /**
     * Display the gallery assets for a specific destination.
     */
    public function index(string $destinationId): JsonResponse
    {
        $destination = Destination::findOrFail($destinationId);

        return response()->json([
            'data' => DestinationMediaResource::collection($destination->media)
        ]);
    }

    /**
     * Synchronize (Bulk Upsert/Delete) gallery items for a specific destination.
     */
    public function sync(
        DestinationMediaRequest $request, 
        SyncDestinationMediaAction $action
    ): JsonResponse {
        $action->execute(
            $request->validated('destination_id'),
            $request->validated('media')
        );

        $destination = Destination::findOrFail($request->validated('destination_id'));

        return response()->json([
            'message' => 'Destination gallery media updated successfully.',
            'data'    => DestinationMediaResource::collection($destination->media)
        ]);
    }
}