<?php

namespace Modules\Geo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Modules\Geo\Actions\GetDestinationTourismItemsAction;
use Modules\Geo\Actions\CreateDestinationTourismItemsAction;
use Modules\Geo\Actions\UpdateDestinationTourismItemsAction;
use Modules\Geo\Actions\DeleteDestinationTourismItemsAction;
use Modules\Geo\Http\Requests\DestinationTourismItemsRequest;
use Modules\Geo\Http\Resources\DestinationTourismItemResource;

class DestinationTourismItemController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all highlights context for a specific destination nodes (Paginated).
     */
    public function index(string $destinationId, GetDestinationTourismItemsAction $action): JsonResponse
    {
        $paginatedItems = $action->execute($destinationId);
        
        // Wrap the paginator data records with your Resource class wrapper
        $resourceCollection = DestinationTourismItemResource::collection($paginatedItems);

        // Your trait will intercept this class structure and automatically separate 'data', 'meta', and 'links'
        return $this->paginatedResponse(
            $resourceCollection,
            'Destination tourism items retrieved successfully.'
        );
    }

    /**
     * Store or update a tourism item entry using item-by-item single requests layout.
     */
    public function save(
        DestinationTourismItemsRequest $request,
        CreateDestinationTourismItemsAction $createAction,
        UpdateDestinationTourismItemsAction $updateAction
    ): JsonResponse {
        $validated = $request->validated();

        if (!empty($validated['id'])) {
            $item = $updateAction->execute($validated['id'], $validated);
            
            return $this->successResponse(
                new DestinationTourismItemResource($item),
                'Destination tourism item updated successfully.'
            );
        }

        $item = $createAction->execute($validated);
        
        return $this->createdResponse(
            new DestinationTourismItemResource($item),
            'Destination tourism item created successfully.'
        );
    }

    /**
     * Delete an isolated point of interest tourism item entry.
     */
    public function destroy(string $id, DeleteDestinationTourismItemsAction $action): JsonResponse
    {
        $action->execute($id);

        return $this->successResponse(
            null,
            'Destination tourism item deleted successfully.'
        );
    }
}