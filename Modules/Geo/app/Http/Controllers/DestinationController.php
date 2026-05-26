<?php

namespace Modules\Geo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Geo\Actions\GetFilteredDestinationsAction;
use Modules\Geo\Actions\GetAllCountriesAction;
use Modules\Geo\Http\Resources\CountryResource;
use Modules\Geo\Actions\UpsertDestinationAction;
use Modules\Geo\Http\Requests\DestinationRequest;
use Modules\Geo\Http\Resources\DestinationResource;
use Modules\Geo\Http\Resources\DestinationDetailResource;
use Modules\Geo\Models\Destination;
use App\Traits\ApiResponseTrait; // 1. Import the trait

class DestinationController extends Controller
{
    use ApiResponseTrait; // 2. Use the trait

   public function index(Request $request, GetFilteredDestinationsAction $action): JsonResponse
    {
        // 1. Fetch the standard paginator from your action class
        $paginator = $action->execute(
            $request->only(['country_id', 'search']),
            $request->get('per_page', 15)
        );

        // 2. Wrap the paginator directly using the API Resource collection helper
        // 💡 This preserves 'meta' and 'links' fields natively out-of-the-box
        $resourceCollection = DestinationResource::collection($paginator);

        // 3. Dispatch the response matching your exact country controller setup
        return $this->paginatedResponse($resourceCollection, 'Destinations retrieved successfully');
    }

    public function countriesLookup(Request $request, GetAllCountriesAction $action): JsonResponse
    {
        $countries = $action->execute();

        // Map through an anonymous resource to structure only the requested fields
        $data = CountryResource::collection($countries)->additional([
            'transform_lookup' => true // A flag if you want to use the same Resource file safely
        ]);

        // Alternatively, use a clean inline mapping array to guarantee your exact shape:
        $formattedData = $countries->map(function ($country) use ($request) {
            return [
                'id'           => $country->id,
                'iso_code'     => $country->iso_code,
                'display_name' => $country->getNameByLocale($request->get('locale', 'en')) ?? $country->id,
            ];
        });

        return $this->successResponse($formattedData, 'Countries lookup list retrieved successfully');
    }

    public function store(DestinationRequest $request, UpsertDestinationAction $action): JsonResponse
    {
        $destination = $action->execute($request->validated());
        
        return $this->createdResponse(
            new DestinationResource($destination), 
            'Destination created successfully'
        );
    }

    public function show(string $id): JsonResponse
    {
        // 2. Load all heavy relationships for the detail view
        $destination = Destination::with(['cities', 'images', 'videos', 'featuredMedia'])
            ->findOrFail($id);
        
        // Use the detailed resource
        return $this->successResponse(
            new DestinationDetailResource($destination),
            'Destination details retrieved'
        );
    }

    public function update(DestinationRequest $request, string $id, UpsertDestinationAction $action): JsonResponse
    {
        $destination = $action->execute($request->validated(), $id);
        
        return $this->successResponse(
            new DestinationResource($destination),
            'Destination updated successfully'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        Destination::findOrFail($id)->delete();
        
        return $this->successResponse(null, 'Destination deleted successfully');
    }
}