<?php

namespace Modules\DayTour\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DayTourImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day_tour_id' => $this->day_tour_id,
            'url' => $this->s3_path,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
            'disk' => $this->disk,
            'created_at' => $this->created_at,
        ];
    }
}
