<?php

namespace Modules\DayTour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadDayTourImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:6'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB max each
            'is_primary' => ['nullable', 'boolean'],
            'queue' => ['nullable', 'string', 'in:images,cache'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'At least one image file is required',
            'images.array' => 'Images must be an array',
            'images.min' => 'At least 1 image is required',
            'images.max' => 'Maximum 6 images allowed per request',
            'images.*.required' => 'All image files are required',
            'images.*.image' => 'All files must be images',
            'images.*.mimes' => 'All images must be JPEG, PNG, JPG, GIF, or WebP',
            'images.*.max' => 'Each image size must not exceed 10MB',
            'queue.in' => 'Queue must be either "images" or "cache"',
        ];
    }
}
