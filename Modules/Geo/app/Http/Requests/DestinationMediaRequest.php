<?php

namespace Modules\Geo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestinationMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_id' => 'required|string|exists:destinations,id',
            
            // Gallery collection validation
            'media'                      => 'required|array|min:1',
            'media.*.type'               => ['required', 'string', Rule::in(['photo', 'video_link'])],
            'media.*.url'                => 'required|url',
            'media.*.sort_order'         => 'nullable|integer|min:0',
            'media.*.is_featured'        => 'required|boolean',
            
            // Caption translations are optional for media items
            'media.*.caption_translations'          => 'nullable|array',
            'media.*.caption_translations.*.locale' => 'required_with:media.*.caption_translations|string',
            'media.*.caption_translations.*.value'  => 'required_with:media.*.caption_translations|string',
        ];
    }

    /**
     * Optional business-rule validation check
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mediaItems = $this->input('media', []);
            $featuredCount = collect($mediaItems)->where('is_featured', true)->count();

            if ($featuredCount > 1) {
                $validator->errors()->add('media', 'A destination can only have exactly one featured cover media item.');
            }
        });
    }
}