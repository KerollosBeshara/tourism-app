<?php

namespace Modules\DayTour\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DayTourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'title' => $this->title,
            'description' => $this->description,
            'city' => [
                'id' => $this->city?->id,
                'name' => $this->city?->name_translations,
            ],
            'destination' => [
                'id' => $this->destination?->id,
                'name' => $this->destination?->name_translations ?? null,
            ],
            'is_active' => $this->is_active,
            'is_shared' => $this->is_shared,
            'primary_image' => new DayTourImageResource($this->primary_image),
            'images_count' => $this->images_count ?? $this->images()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
