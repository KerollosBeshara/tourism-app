<?php

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
      return [
            'mediable_id' => 'required|string',
            'mediable_type' => 'required|string',
            'items' => 'required|array',
            'items.*.id' => 'nullable|string|exists:media,id',
            'items.*.type' => 'required|string|in:image,video_link',
            'items.*.collection_name' => 'required|string|max:50',
            
            'items.*.file' => [
                'nullable',
                'file',
                'max:10240',
                // Prohibit file if type is video_link
                'prohibited_if:items.*.type,video_link',
                // Require file if type is image AND there is no ID
                // We use a closure for complex logic
                function ($attribute, $value, $fail) {
                    // Get the index from the attribute (e.g., "items.0.file" -> 0)
                    preg_match('/items\.(\d+)\.file/', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    
                    if ($index !== null) {
                        $item = request()->input("items.{$index}");
                        $isImage = ($item['type'] ?? '') === 'image';
                        $hasId = !empty($item['id']);
                        
                        // If it is an image and has no ID, the file is required
                        if ($isImage && !$hasId && empty($value)) {
                            $fail("The file is required for new images.");
                        }
                    }
                },
            ],
            
            'items.*.video_url' => [
                'required_if:items.*.type,video_link',
                'nullable',
                'url',
            ],
        ];
    }
}