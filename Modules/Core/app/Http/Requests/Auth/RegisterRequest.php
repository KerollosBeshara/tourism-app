<?php

namespace Modules\Core\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:accounts,email',
            'password' => 'required|min:8|confirmed',
            'agency_name' => 'required|string|max:255', // SaaS requires an agency
            'full_name' => 'required|string|max:255',
            'country_id' => 'required|string|exists:countries,id', // Country must exist
            'base_currency_id' => 'nullable|string|exists:currencies,id', // Currency must exist if provided
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
