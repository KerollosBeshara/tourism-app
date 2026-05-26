<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DestinationMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'destination_id'       => $this->destination_id,
            'type'                 => $this->type,
            'url'                  => $this->url,
            'sort_order'           => $this->sort_order,
            'is_featured'          => $this->is_featured,
            'caption_translations' => $this->caption_translations,
            'caption'              => $this->getCaption(),
        ];
    }
}