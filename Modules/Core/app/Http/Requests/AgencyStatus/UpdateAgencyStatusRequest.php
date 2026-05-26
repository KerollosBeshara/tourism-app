<?php

namespace Modules\Core\Http\Requests\AgencyStatus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name_translations' => 'sometimes|required|array',
            'name_translations.*.locale' => 'required|string|max:5',
            'name_translations.*.value' => 'required|string|max:255',
            'color_code' => 'nullable|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name_translations.required' => 'Name translations are required.',
            'name_translations.array' => 'Name translations must be an array.',
            'name_translations.*.locale.required' => 'Each translation must have a locale.',
            'name_translations.*.value.required' => 'Each translation must have a value.',
            'color_code.regex' => 'Color code must be a valid hex color (e.g., #FFFFFF or #FFF).',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validated data.
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        if (is_array($data)) {
            $data['is_active'] = $this->has('is_active') ? $this->boolean('is_active') : null;
        }

        return $data;
    }
}
