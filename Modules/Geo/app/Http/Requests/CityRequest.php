<?php

namespace Modules\Geo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('city');

        return [
            'country_id' => 'required|string|exists:countries,id',
            
            // Ensure name_translations is a structured array list
            'name_translations' => 'required|array|min:1',
            
            // 💡 Use wildcard dot notation to validate the properties inside your list items
            'name_translations.*.locale' => 'required|string',
            'name_translations.*.value' => 'required|string',
            
            // Custom Rule hook to ensure 'en' stays mandatory in the collection
            'name_translations' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $locales = collect($value)->pluck('locale')->toArray();
                    if (!in_array('en', $locales)) {
                        $fail('The English language translation field is required.');
                    }
                },
            ],
            
            'slug' => [
                'required',
                'string',
                Rule::unique('cities', 'slug')->ignore($id)
            ],
            'timezone' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'meta' => 'nullable|array',
        ];
    }
}