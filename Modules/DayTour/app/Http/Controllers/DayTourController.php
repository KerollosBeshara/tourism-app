<?php

namespace Modules\DayTour\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DayTour\Actions\CreateDayTourAction;
use Modules\DayTour\Actions\UpdateDayTourAction;
use Modules\DayTour\Actions\UploadDayTourImageAction;
use Modules\DayTour\Http\Requests\StoreDayTourRequest;
use Modules\DayTour\Http\Requests\UpdateDayTourRequest;
use Modules\DayTour\Http\Requests\UploadDayTourImageRequest;
use Modules\DayTour\Http\Resources\DayTourResource;
use Modules\DayTour\Http\Resources\DayTourImageResource;
use Modules\DayTour\Repositories\DayTourRepository;
use Illuminate\Http\JsonResponse;

class DayTourController extends Controller
{
    public function __construct(
        private DayTourRepository $repository,
        private CreateDayTourAction $createAction,
        private UpdateDayTourAction $updateAction,
        private UploadDayTourImageAction $uploadImageAction,
    ) {
    }

    /**
     * List all day tours
     */
    public function index(): JsonResponse
    {
        $dayTours = $this->repository->getActive();

        return response()->json([
            'data' => DayTourResource::collection($dayTours->items()),
            'meta' => [
                'current_page' => $dayTours->currentPage(),
                'total' => $dayTours->total(),
                'per_page' => $dayTours->perPage(),
                'last_page' => $dayTours->lastPage(),
            ],
        ]);
    }

    /**
     * Create new day tour
     */
    public function store(StoreDayTourRequest $request): JsonResponse
    {
        $dayTour = $this->createAction->execute($request->validated());

        return response()->json(
            new DayTourResource($dayTour),
            201
        );
    }

    /**
     * Get specific day tour
     */
    public function show(string $id): JsonResponse
    {
        $dayTour = $this->repository->findById($id);

        if (!$dayTour) {
            return response()->json(['message' => 'Day tour not found'], 404);
        }

        return response()->json(new DayTourResource($dayTour));
    }

    /**
     * Update day tour
     */
    public function update(UpdateDayTourRequest $request, string $id): JsonResponse
    {
        $dayTour = $this->repository->findById($id);

        if (!$dayTour) {
            return response()->json(['message' => 'Day tour not found'], 404);
        }

        $updated = $this->updateAction->execute($dayTour, $request->validated());

        return response()->json(new DayTourResource($updated));
    }

    /**
     * Delete day tour
     */
    public function destroy(string $id): JsonResponse
    {
        $dayTour = $this->repository->findById($id);

        if (!$dayTour) {
            return response()->json(['message' => 'Day tour not found'], 404);
        }

        $this->repository->delete($dayTour);

        return response()->json(['message' => 'Day tour deleted successfully']);
    }

    /**
     * Upload image to day tour (async)
     */
    public function uploadImage(UploadDayTourImageRequest $request, string $dayTourId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return response()->json(['message' => 'Day tour not found'], 404);
        }

        $image = $this->uploadImageAction->execute(
            $dayTour,
            $request->file('image'),
            $request->boolean('is_primary', false),
            $request->input('sort_order'),
            $request->input('queue', 'images')
        );

        return response()->json([
            'data' => new DayTourImageResource($image),
            'message' => 'Image upload queued for processing',
        ], 202); // Accepted (async processing)
    }

    /**
     * List day tour images
     */
    public function listImages(string $dayTourId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return response()->json(['message' => 'Day tour not found'], 404);
        }

        return response()->json([
            'data' => DayTourImageResource::collection($dayTour->images),
        ]);
    }

    /**
     * Delete image from day tour (async)
     */
    public function deleteImage(string $dayTourId, int $imageId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return response()->json(['message' => 'Day tour not found'], 404);
        }

        $image = $dayTour->images()->find($imageId);

        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        // Dispatch delete job
        \Modules\DayTour\Jobs\DeleteDayTourImageJob::dispatch($image)
            ->onQueue('images');

        return response()->json([
            'message' => 'Image deletion queued for processing',
        ], 202); // Accepted (async processing)
    }

    /**
     * Search day tours
     */
    public function search(): JsonResponse
    {
        $filters = request()->only(['agency_id', 'city_id', 'destination_id', 'search']);
        $dayTours = $this->repository->search($filters);

        return response()->json([
            'data' => DayTourResource::collection($dayTours->items()),
            'meta' => [
                'current_page' => $dayTours->currentPage(),
                'total' => $dayTours->total(),
                'per_page' => $dayTours->perPage(),
            ],
        ]);
    }
}
