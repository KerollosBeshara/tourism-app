<?php

namespace Modules\DayTour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDayTourImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB max
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'queue' => ['nullable', 'string', 'in:images,cache'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Image file is required',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Image must be JPEG, PNG, JPG, GIF, or WebP',
            'image.max' => 'Image size must not exceed 10MB',
            'queue.in' => 'Queue must be either "images" or "cache"',
        ];
    }
}
