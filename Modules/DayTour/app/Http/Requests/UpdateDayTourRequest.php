<?php

namespace Modules\DayTour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDayTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'title_translations' => ['nullable', 'array'],
            'title_translations.*.locale' => ['required_with:title_translations', 'string', 'in:en,ar'],
            'title_translations.*.value' => ['required_with:title_translations', 'string', 'min:3', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.*.locale' => ['required_with:description_translations', 'string', 'in:en,ar'],
            'description_translations.*.value' => ['required_with:description_translations', 'string', 'min:10', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'is_shared' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.exists' => 'The selected city does not exist',
            'destination_id.exists' => 'The selected destination does not exist',
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();
        
        if (isset($data['title_translations'])) {
            $data['title_translations'] = $this->normalizeTranslations($data['title_translations']);
        }
        
        if (isset($data['description_translations'])) {
            $data['description_translations'] = $this->normalizeTranslations($data['description_translations']);
        }

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
