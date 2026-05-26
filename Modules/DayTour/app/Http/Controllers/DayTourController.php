<?php

namespace Modules\DayTour\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Modules\DayTour\Actions\CreateDayTourAction;
use Modules\DayTour\Actions\UpdateDayTourAction;
use Modules\DayTour\Actions\UploadDayTourImageAction;
use Modules\DayTour\Http\Requests\StoreDayTourRequest;
use Modules\DayTour\Http\Requests\UpdateDayTourRequest;
use Modules\DayTour\Http\Requests\UploadDayTourImageRequest;
use Modules\DayTour\Http\Requests\BulkUploadDayTourImageRequest;
use Modules\DayTour\Http\Resources\DayTourResource;
use Modules\DayTour\Http\Resources\DayTourImageResource;
use Modules\DayTour\Repositories\DayTourRepository;
use Illuminate\Http\JsonResponse;

class DayTourController extends Controller
{
    use ApiResponseTrait;
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

        return $this->createdResponse(
            new DayTourResource($dayTour),
            'Day tour created successfully'
        );
    }

    /**
     * Get specific day tour
     */
    public function show(string $id): JsonResponse
    {
        $dayTour = $this->repository->findById($id);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        return $this->successResponse(new DayTourResource($dayTour));
    }

    /**
     * Update day tour
     */
    public function update(UpdateDayTourRequest $request, string $id): JsonResponse
    {
        $dayTour = $this->repository->findById($id);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        $updated = $this->updateAction->execute($dayTour, $request->validated());

        return $this->successResponse(
            new DayTourResource($updated),
            'Day tour updated successfully'
        );
    }

    /**
     * Delete day tour
     */
    public function destroy(string $id): JsonResponse
    {
        $dayTour = $this->repository->findById($id);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        $this->repository->delete($dayTour);

        return $this->successResponse(null, 'Day tour deleted successfully');
    }

    /**
     * Upload image to day tour (async)
     */
    public function uploadImage(UploadDayTourImageRequest $request, string $dayTourId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        // Check if day tour already has 6 images
        if ($dayTour->images->count() >= 6) {
            return $this->errorResponse(
                'Maximum 6 images already uploaded for this day tour',
                422
            );
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
     * Bulk upload images to day tour (max 6 per request, max 6 total per day tour)
     */
    public function bulkUploadImages(BulkUploadDayTourImageRequest $request, string $dayTourId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        $currentImageCount = $dayTour->images->count();
        $requestedImageCount = count($request->file('images'));
        $totalImageCount = $currentImageCount + $requestedImageCount;

        // Check if total would exceed 6 images
        if ($totalImageCount > 6) {
            return $this->errorResponse(
                "Cannot upload {$requestedImageCount} images. Day tour already has {$currentImageCount} images. Maximum total is 6 images.",
                422
            );
        }

        // Mark first as primary only if no primary image exists and it's the first batch
        $shouldMarkFirstAsPrimary = $currentImageCount === 0;

        $images = $this->uploadImageAction->uploadBatch(
            $dayTour,
            $request->file('images'),
            $shouldMarkFirstAsPrimary,
            $request->input('queue', 'images')
        );

        return response()->json([
            'data' => DayTourImageResource::collection($images),
            'message' => count($images) . ' images queued for processing',
        ], 202); // Accepted (async processing)
    }

    /**
     * List day tour images
     */
    public function listImages(string $dayTourId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        return $this->successResponse(
            DayTourImageResource::collection($dayTour->images),
            'Day tour images retrieved successfully'
        );
    }

    /**
     * Delete image from day tour (async)
     */
    public function deleteImage(string $dayTourId, int $imageId): JsonResponse
    {
        $dayTour = $this->repository->findById($dayTourId);

        if (!$dayTour) {
            return $this->notFoundResponse('Day tour not found');
        }

        $image = $dayTour->images()->find($imageId);

        if (!$image) {
            return $this->notFoundResponse('Image not found');
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
