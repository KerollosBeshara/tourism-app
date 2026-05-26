<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DestinationTourismItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'destination_id'           => $this->destination_id,
            'sort_order'               => $this->sort_order,
            'icon'                     => $this->icon,
            'title_translations'       => $this->title_translations,
            'description_translations' => $this->description_translations,
            // Helper translations parsed down for immediate UI use
            'title'                    => $this->getTitle(),
            'description'              => $this->getDescription(),
        ];
    }
}