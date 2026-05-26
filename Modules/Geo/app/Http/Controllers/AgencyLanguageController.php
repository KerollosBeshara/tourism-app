<?php

namespace Modules\Geo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Geo\Models\AgencyLanguage;
use Modules\Geo\Http\Requests\AgencyLanguageRequest;
use Modules\Geo\Actions\CreateAgencyLanguageAction;
use Modules\Geo\Actions\UpdateAgencyLanguageAction;
use Modules\Geo\Actions\DeleteAgencyLanguageAction;
use Modules\Geo\Actions\GetAllLanguagesAction;
use App\Http\Controllers\Controller;
use Modules\Geo\Http\Resources\AgencyLanguageResource;
use Modules\Geo\Http\Resources\LanguageResource;
use Modules\Geo\Http\Resources\AgencyLanguageCollection;
use App\Traits\ApiResponseTrait; // 1. Import the trait

class AgencyLanguageController extends Controller
{
    use ApiResponseTrait; // 2. Use the trait

    public function __construct(
        private CreateAgencyLanguageAction $createAction,
        private UpdateAgencyLanguageAction $updateAction,
        private DeleteAgencyLanguageAction $deleteAction,
        private GetAllLanguagesAction $getAllLanguagesAction
    ) {}

   public function index(Request $request): JsonResponse
    {
        if (!$this->checkAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $query = AgencyLanguage::with(['agency', 'language']);

        // Filtering Logic
        $query->when($request->agency_id, fn($q) => $q->where('agency_id', $request->agency_id))
            ->when($request->language_id, fn($q) => $q->where('language_id', $request->language_id))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')));

        // 1. Get the raw Eloquent Paginator
        $paginator = $query->paginate($request->get('per_page', 15));

        // 2. Wrap the paginator data using your individual item resource.
        // Laravel paginators are "Resourceable", meaning preserving pagination metadata is built-in.
        $resourceCollection = AgencyLanguageResource::collection($paginator);

        // 3. Pass it cleanly to your trait's paginated response handler
        return $this->paginatedResponse($resourceCollection);
    }

    public function store(AgencyLanguageRequest $request): JsonResponse
    {
        if (!$this->checkAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $agencyLanguage = $this->createAction->execute($request->validated());

        return $this->createdResponse(
            new AgencyLanguageResource($agencyLanguage->load(['agency', 'language'])),
            'Agency language assigned successfully'
        );
    }

    

    public function update(AgencyLanguageRequest $request, AgencyLanguage $agencyLanguage): JsonResponse
    {
        if (!$this->checkAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $updated = $this->updateAction->execute($agencyLanguage, $request->validated());

        return $this->successResponse(
            new AgencyLanguageResource($updated->load(['agency', 'language'])),
            'Agency language updated successfully'
        );
    }

    

    public function bulkDelete(Request $request): JsonResponse
    {
        if (!$this->checkAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string|exists:agency_languages,id',
        ]);

        $this->deleteAction->execute(collect($request->ids));

        return $this->successResponse(null, 'Selected records deleted successfully');
    }

    public function languages(Request $request): JsonResponse
    {
        if (!$this->checkAuth($request)) {
            return $this->unauthorizedResponse();
        }

        $languages = $this->getAllLanguagesAction->execute();

        return $this->successResponse(
            LanguageResource::collection($languages),
            'Languages retrieved successfully'
        );
    }

    private function checkAuth(Request $request): bool
    {
        return !empty($request->header('Authorization'));
    }
}