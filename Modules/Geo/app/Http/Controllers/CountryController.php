<?php

namespace Modules\Geo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Geo\Models\Country;
use Modules\Geo\Http\Requests\CountryRequest;
use Modules\Geo\Http\Resources\CountryResource;
use App\Traits\ApiResponseTrait;

// Actions mapping layer references
use Modules\Geo\Actions\GetCountriesAction;
use Modules\Geo\Actions\CreateCountryAction;
use Modules\Geo\Actions\UpdateCountryAction;
use Modules\Geo\Actions\DeleteCountryAction;

class CountryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private GetCountriesAction $getCountriesAction,
        private CreateCountryAction $createCountryAction,
        private UpdateCountryAction $updateCountryAction,
        private DeleteCountryAction $deleteCountryAction
    ) {}

    /**
     * Display a paginated listing of country records filtered by search keywords.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->get('per_page', 15);

        $paginatedCountries = $this->getCountriesAction->execute($filters, (int) $perPage);
        
        // Wrap using individual item resource mappings
        $resourceCollection = CountryResource::collection($paginatedCountries);

        return $this->paginatedResponse($resourceCollection, 'Countries records list generated successfully.');
    }

    /**
     * Store a newly created country record in storage.
     */
    public function store(CountryRequest $request): JsonResponse
    {
        $country = $this->createCountryAction->execute($request->validated());

        return $this->createdResponse(
            new CountryResource($country),
            'Country profile initialized successfully.'
        );
    }

    /**
     * Update the specified country record parameters in storage.
     */
    public function update(CountryRequest $request, Country $country): JsonResponse
    {
        $updatedCountry = $this->updateCountryAction->execute($country, $request->validated());

        return $this->successResponse(
            new CountryResource($updatedCountry),
            'Country translation and ISO parameters updated successfully.'
        );
    }

    /**
     * Remove the specified country configurations from persistent engine storage.
     */
    public function destroy(Country $country): JsonResponse
    {
        $this->deleteCountryAction->execute($country);

        return $this->successResponse(
            null,
            'Country reference schema record dropped successfully.'
        );
    }
}