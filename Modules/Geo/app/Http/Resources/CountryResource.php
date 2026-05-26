<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iso_code' => $this->iso_code,
            'emoji_flag' => $this->emoji_flag,
            'name_translations' => $this->name_translations,
            // Computed helper payload element optimization mapping to default application translation runtime context
            'display_name' => $this->getNameByLocale($request->get('locale', 'en')) ?? $this->id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}