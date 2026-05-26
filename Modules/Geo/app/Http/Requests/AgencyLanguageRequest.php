<?php

namespace Modules\Geo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgencyLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Prepare the data for validation.
     * Normalizes loose boolean parameters coming from different frontend state layouts.
     */
    protected function prepareForValidation(): void
    {
        // Maps missing properties from incoming camelCase fields (isActive/isFeatured) if present
        $this->merge([
            'is_active' => $this->has('isActive') ? $this->isActive : $this->is_active,
            'is_default' => $this->has('isFeatured') ? $this->isFeatured : $this->is_default,
        ]);

        // Cast loose inputs explicitly into standard boolean structures 
        $this->merge([
            'is_default' => filter_var($this->is_default, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'is_active'  => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $agencyLanguageId = $this->route('agency_language')?->id ?? null;

        return [
            'agency_id' => [
                'required',
                'string',
                'exists:agencies,id',
                Rule::unique('agency_languages', 'agency_id')
                    ->where('language_id', $this->language_id)
                    ->ignore($agencyLanguageId),
            ],
            'language_id' => 'required|string|exists:languages,id',
            'is_default'  => 'required|boolean', // Safe validation check after sanitization
            'is_active'   => 'required|boolean', // Safe validation check after sanitization
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'agency_id.unique' => 'This agency already has this language assigned.',
        ];
    }
}