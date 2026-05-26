<?php

namespace Modules\Geo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPost = $this->isMethod('post');
        
        // Safely extract the country model or ID from the route parameter
        $country = $this->route('country');
        $countryId = is_object($country) ? $country->id : $country;

        return [
            // The backend handles ID generation now; removed 'id' completely from validation rules
            
            'iso_code' => [
                'required',
                'string',
                'min:2',
                'max:3',
                // Cleaner native approach to handle unique validation for both Store and Update loops
                $isPost 
                    ? Rule::unique('countries', 'iso_code') 
                    : Rule::unique('countries', 'iso_code')->ignore($countryId),
            ],
            
            'emoji_flag' => 'nullable|string|max:10',
            
            // Nested structural translation array checks
            'name_translations' => 'required|array|min:1',
            'name_translations.*.locale' => 'required|string|max:5',
            'name_translations.*.value' => 'required|string|max:255',
        ];
    }
    
    /**
     * Optional: Automatically format inputs before passing to validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('iso_code')) {
            $this->merge([
                'iso_code' => strtoupper($this->iso_code),
            ]);
        }
    }
}