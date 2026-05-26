<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        // Convert to a collection or wrap in collect() if it's a raw array
        $translations = collect($this->name_translations);

        // Find the object where 'locale' is 'en' and grab its 'value'
        $englishName = $translations->firstWhere('locale', 'en')['value'] ?? '';

        return [
            'id' => $this->id,
            'name' => $englishName, // 🧠 Now securely resolves to "Tbilisi"
            'name_translations' => $this->name_translations,
            'slug' => $this->slug,
            'timezone' => $this->timezone,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'meta' => $this->meta,
        ];
    }
}