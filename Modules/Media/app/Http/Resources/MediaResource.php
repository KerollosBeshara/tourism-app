<?php

namespace Modules\Media\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'model_type'      => $this->mediable_type,
            'collection_name' => $this->collection_name,
            'name'            => $this->name,
            'file_name'       => $this->file_name,
            'mime_type'       => $this->mime_type,
            'size'            => (int) $this->size,
            
            // Assuming your Media model has an 'url' accessor or attribute
            // If you use S3, this should return the full signed/public URL
            'url'             => $this->url, 
            
            // Only include if you have a specific embed field for videos
            'embed_url'       => $this->when($this->type === 'video', $this->embed_url),
            
            'sort_order'      => (int) $this->sort_order,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}