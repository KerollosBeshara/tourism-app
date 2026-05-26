<?php

namespace Modules\Geo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Geo\Actions\UpsertCityAction;
use Modules\Geo\Actions\GetCitiesAction; // ◄ Imported the new Action
use Modules\Geo\Http\Requests\CityRequest;
use Modules\Geo\Http\Resources\CityResource;
use Modules\Geo\Models\City;
use App\Traits\ApiResponseTrait;

class CityController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a paginated list of cities with optional filters.
     */
   public function index(Request $request, GetCitiesAction $action): JsonResponse
    {
        // Extract both filters: name and country_id
        $filters = $request->only(['name', 'country_id']);
        $perPage = $request->get('per_page', 15);

        // Execute the action with filters and per_page limit
        $paginatedCities = $action->execute(array_merge($filters, ['per_page' => (int) $perPage]));
        
        // Wrap using individual item resource mappings
        $resourceCollection = CityResource::collection($paginatedCities);

        // Return using your trait's built-in paginated layout handler
        return $this->paginatedResponse(
            $resourceCollection, 
            'Cities records list generated successfully.'
        );
    }

    public function store(CityRequest $request, UpsertCityAction $action): JsonResponse
    {
        $city = $action->execute($request->validated());
        
        return $this->createdResponse(
            new CityResource($city), 
            'City created successfully'
        );
    }

    public function show(string $id): JsonResponse
    {
        $city = City::findOrFail($id);
        
        return $this->successResponse(
            new CityResource($city), 
            'City details retrieved'
        );
    }

    public function update(CityRequest $request, string $id, UpsertCityAction $action): JsonResponse
    {
        $city = $action->execute($request->validated(), $id);
        
        return $this->successResponse(
            new CityResource($city), 
            'City updated successfully'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        City::findOrFail($id)->delete();
        
        return $this->successResponse(null, 'City deleted successfully');
    }
}