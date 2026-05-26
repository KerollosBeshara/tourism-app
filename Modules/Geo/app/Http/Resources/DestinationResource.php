<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Media\Http\Resources\MediaResource;
use Modules\Geo\Http\Resources\CityResource;

class DestinationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'is_active'                => (bool) $this->is_active,
            'agency_id'                => $this->agency_id,
            'country_id'               => $this->country_id,
            'country_code'             => $this->country_code,
            'slug'                     => $this->slug,
            'title_translations'       => $this->title_translations,
            'description_translations' => $this->description_translations,
            'latitude'                 => !is_null($this->latitude) ? (float) $this->latitude : null,
            'longitude'                => !is_null($this->longitude) ? (float) $this->longitude : null,
            'map_data'                 => $this->map_data,
            'geojson'                  => $this->geojson,
            'regional_data'            => $this->regional_data,
            'view_count'               => (int) $this->view_count,
            
            
            // // Relationships
            // 'images'         => MediaResource::collection($this->whenLoaded('images')),
            // 'videos'         => MediaResource::collection($this->whenLoaded('videos')),
            // 'featured_media' => new MediaResource($this->whenLoaded('featuredMedia')),
            // 'cities'         => CityResource::collection($this->whenLoaded('cities')),
            
            // 'created_at'     => $this->created_at?->toIso8601String(),
            // 'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}