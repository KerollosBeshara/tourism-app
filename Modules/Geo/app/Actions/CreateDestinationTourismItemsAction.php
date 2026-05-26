<?php

namespace Modules\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Geo\Models\Destination;
use Modules\Geo\Models\DestinationTourismItem;

class CreateDestinationTourismItemsAction
{
    /**
     * Execute atomic write operations for storing a new tourism item profile.
     */
    public function execute(array $data): DestinationTourismItem
    {
        return DB::transaction(function () use ($data) {
            $destination = Destination::findOrFail($data['destination_id']);

            if (!isset($data['sort_order'])) {
                $data['sort_order'] = $destination->tourismItems()->max('sort_order') + 1;
            }

            return $destination->tourismItems()->create([
                'icon'                     => $data['icon'] ?? null,
                'sort_order'               => $data['sort_order'],
                'title_translations'       => $data['title_translations'],
                'description_translations' => $data['description_translations'],
            ]);
        });
    }
}