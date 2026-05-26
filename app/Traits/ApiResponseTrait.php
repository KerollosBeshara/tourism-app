<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponseTrait
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $code = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a paginated response.
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($data, string $message = 'Success'): JsonResponse
    {
        // If it's a custom Resource Collection class, resolve it directly
        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            $resolved = $data->response()->getData(true);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $resolved['data'] ?? [],
                'meta'    => $resolved['meta'] ?? null,
                'links'   => $resolved['links'] ?? null,
            ], Response::HTTP_OK);
        }

        // Fallback for raw Eloquent LengthAwarePaginator instances
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => method_exists($data, 'items') ? $data->items() : $data,
            'pagination' => [
                'current_page' => method_exists($data, 'currentPage') ? $data->currentPage() : null,
                'last_page'    => method_exists($data, 'lastPage') ? $data->lastPage() : null,
                'per_page'     => method_exists($data, 'perPage') ? $data->perPage() : null,
                'total'        => method_exists($data, 'total') ? $data->total() : null,
                'from'         => method_exists($data, 'firstItem') ? $data->firstItem() : null,
                'to'           => method_exists($data, 'lastItem') ? $data->lastItem() : null,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Return a no content response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function noContentResponse(string $message = 'No Content'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a created response.
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Created'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_CREATED);
    }

    /**
     * Return an unauthorized response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a not found response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Not Found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return a validation error response.
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse($errors, string $message = 'Validation Error'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }
}
