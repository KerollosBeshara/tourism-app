<?php
namespace Modules\Geo\Http\Resources;

use Modules\Media\Http\Resources\MediaResource;
use Modules\Geo\Http\Resources\CityResource;

class DestinationDetailResource extends DestinationResource
{
    public function toArray($request): array
    {
        // We call parent::toArray to get the core fields + list fields,
        // then merge the specific heavy details.
        return array_merge(parent::toArray($request), [
            'agency_id'                => $this->agency_id,
            'slug'                     => $this->slug,

            'country_id'               => $this->country_id,
            'country_code'             => $this->country_code,
            'description_translations' => $this->description_translations,
            'latitude'                 => !is_null($this->latitude) ? (float) $this->latitude : null,
            'longitude'                => !is_null($this->longitude) ? (float) $this->longitude : null,
            'map_data'                 => $this->map_data,
            'geojson'                  => $this->geojson,
            'regional_data'            => $this->regional_data,
            
            // Full relationships
            'images'         => MediaResource::collection($this->whenLoaded('images')),
            'featured_media' => new MediaResource($this->whenLoaded('featuredMedia')),
            'videos'         => MediaResource::collection($this->whenLoaded('videos')),
            'cities'         => CityResource::collection($this->whenLoaded('cities')),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ]);
    }
}