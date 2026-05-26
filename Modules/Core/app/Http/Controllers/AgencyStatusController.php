<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\AgencyStatus;
use Modules\Core\Http\Requests\AgencyStatus\StoreAgencyStatusRequest;
use Modules\Core\Http\Requests\AgencyStatus\UpdateAgencyStatusRequest;
use Modules\Core\Http\Resources\AgencyStatusResource;
use Modules\Core\Http\Resources\AgencyStatusCollection;
use Illuminate\Support\Str;

class AgencyStatusController extends Controller
{
    /**
     * Display a listing of agency statuses.
     */
    public function index(Request $request): AgencyStatusCollection
    {
        $query = AgencyStatus::query();

        if ($request->has('active')) {
            $query->active();
        }

        $statuses = $query->ordered()->paginate(
            $request->get('per_page', 15)
        );

        return new AgencyStatusCollection($statuses);
    }

    /**
     * Store a newly created agency status in storage.
     */
    public function store(StoreAgencyStatusRequest $request): JsonResponse
    {
        $agencyStatus = AgencyStatus::create([
            'id' => (string) Str::ulid(),
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Agency status created successfully.',
            'data' => new AgencyStatusResource($agencyStatus),
        ], 201);
    }

    /**
     * Display the specified agency status.
     */
    public function show(string $id): JsonResponse
    {
        $agencyStatus = AgencyStatus::findOrFail($id);

        return response()->json([
            'data' => new AgencyStatusResource($agencyStatus),
        ]);
    }

    /**
     * Update the specified agency status in storage.
     */
    public function update(UpdateAgencyStatusRequest $request, string $id): JsonResponse
    {
        $agencyStatus = AgencyStatus::findOrFail($id);
        
        $data = $request->validated();
        
        $agencyStatus->update(array_filter($data, fn($value) => $value !== null));

        return response()->json([
            'message' => 'Agency status updated successfully.',
            'data' => new AgencyStatusResource($agencyStatus),
        ]);
    }

    /**
     * Remove the specified agency status from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $agencyStatus = AgencyStatus::findOrFail($id);
        $agencyStatus->delete();

        return response()->json([
            'message' => 'Agency status deleted successfully.',
        ]);
    }

    /**
     * Bulk update status (activate/deactivate).
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|string|exists:agency_statuses,id',
            'is_active' => 'required|boolean',
        ]);

        AgencyStatus::whereIn('id', $validated['ids'])
            ->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'message' => 'Agency statuses updated successfully.',
        ]);
    }
}
