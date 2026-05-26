<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyLanguageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'language_id' => $this->language_id,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'language' => $this->whenLoaded('language', function() {
                return [
                    'id' => $this->language->id,
                    'name' => $this->language->name,
                ];
            }),
        ];
    }
}