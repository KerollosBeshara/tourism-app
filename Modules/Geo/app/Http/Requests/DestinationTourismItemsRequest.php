<?php

namespace Modules\Geo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestinationTourismItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // If an ID is present, ensure it belongs to this destination to prevent cross-destination data mutation
            'id' => [
                'nullable',
                'string',
                Rule::exists('destination_tourism_items', 'id')->where(function ($query) {
                    return $query->where('destination_id', $this->input('destination_id'));
                }),
            ],
            
            'destination_id' => 'required|string|exists:destinations,id',
            'icon'           => 'nullable|string|max:255',
            'sort_order'     => 'nullable|integer|min:0',
            
            // Explicit Title Translations Matrices
            'title_translations'                      => 'required|array|min:1',
            'title_translations.*.language_id'        => 'required|string',
            'title_translations.*.locale'             => 'required|string|distinct',
            'title_translations.*.value'              => 'required|string',

            // Explicit Description Translations Matrices
            'description_translations'                => 'required|array|min:1',
            'description_translations.*.language_id'  => 'required|string',
            'description_translations.*.locale'       => 'required|string|distinct',
            'description_translations.*.value'        => 'required|string',
        ];
    }
}