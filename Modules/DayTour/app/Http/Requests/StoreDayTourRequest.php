<?php

namespace Modules\DayTour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDayTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agency_id' => ['required', 'uuid', 'exists:agencies,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'title_translations' => ['required', 'array', 'min:1'],
            'title_translations.*.locale' => ['required', 'string', 'in:en,ar'],
            'title_translations.*.value' => ['required', 'string', 'min:3', 'max:255'],
            'description_translations' => ['required', 'array', 'min:1'],
            'description_translations.*.locale' => ['required', 'string', 'in:en,ar'],
            'description_translations.*.value' => ['required', 'string', 'min:10', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'is_shared' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'agency_id.required' => 'Agency ID is required',
            'agency_id.uuid' => 'Agency ID must be a valid UUID',
            'city_id.required' => 'City is required',
            'destination_id.required' => 'Destination is required',
            'title_translations.required' => 'Title translations are required',
            'title_translations.*.value.min' => 'Title must be at least 3 characters',
            'description_translations.required' => 'Description translations are required',
            'description_translations.*.value.min' => 'Description must be at least 10 characters',
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();
        
        // Ensure translations are properly formatted
        $data['title_translations'] = $this->normalizeTranslations($data['title_translations'] ?? []);
        $data['description_translations'] = $this->normalizeTranslations($data['description_translations'] ?? []);

        return $data;
    }

    private function normalizeTranslations(array $translations): array
    {
        return collect($translations)
            ->map(fn($t) => [
                'locale' => $t['locale'] ?? 'en',
                'value' => trim($t['value'] ?? ''),
            ])
            ->toArray();
    }
}
