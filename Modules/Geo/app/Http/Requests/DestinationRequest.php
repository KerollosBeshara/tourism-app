<?php

namespace Modules\Geo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('destination');

        return [
            'agency_id'     => 'required|string|exists:agencies,id',
            'country_id'    => 'required|string|exists:countries,id',
            'slug'          => ['required', 'string', Rule::unique('destinations', 'slug')->ignore($id)],
            'country_code'  => 'required|string|max:5',
            'is_active'     => 'nullable|boolean',
            'view_count'    => 'nullable|integer|min:0',
            
            // Decimal rules matching precision parameters
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            
            // JSONB structural fields
            'map_data'      => 'nullable|array',
            'geojson'       => 'nullable|array',
            'regional_data' => 'nullable|array',

            // Title Translations array payload validation
            'title_translations'          => 'required|array|min:1',
            'title_translations.*.locale' => 'required|string|distinct',
            'title_translations.*.value'  => 'required|string',

            // Description Translations array payload validation
            'description_translations'          => 'required|array|min:1',
            'description_translations.*.locale' => 'required|string|distinct',
            'description_translations.*.value'  => 'required|string',
        ];
    }
}