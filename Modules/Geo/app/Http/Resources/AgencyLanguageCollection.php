<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AgencyLanguageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. Only map the inner data items here.
        return [
            'data' => AgencyLanguageResource::collection($this->collection)
        ];
    }

    /**
     * Customize the outgoing response data.
     * 
     * This is the native, ideal Laravel hook to inject custom links and meta 
     * structures into the root response envelope WITHOUT data duplication conflicts.
     */
    public function withResponse(Request $request, \Illuminate\Http\JsonResponse $response): void
    {
        $originalData = $response->getData(true);

        // 2. Re-map the structure cleanly to match your template requirements
        $response->setData([
            'success' => true,
            'message' => 'Agency languages retrieved successfully', // Fallback context
            'data'    => $originalData['data'] ?? [],
            'meta'    => [
                'total'        => $originalData['meta']['total'] ?? 0,
                'per_page'     => $originalData['meta']['per_page'] ?? 15,
                'current_page' => $originalData['meta']['current_page'] ?? 1,
                'last_page'    => $originalData['meta']['last_page'] ?? 1,
                'from'         => $originalData['meta']['from'] ?? null,
                'to'           => $originalData['meta']['to'] ?? null,
            ],
            'links'   => [
                'first' => $originalData['links']['first'] ?? null,
                'last'  => $originalData['links']['last'] ?? null,
                'prev'  => $originalData['links']['prev'] ?? null,
                'next'  => $originalData['links']['next'] ?? null,
            ]
        ]);
    }
}