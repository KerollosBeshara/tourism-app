<?php

namespace Modules\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Geo\Models\DestinationTourismItem;

class UpdateDestinationTourismItemsAction
{
    /**
     * Perform isolated delta patch mutations on a specific operational tourism item node.
     */
    public function execute(string $id, array $data): DestinationTourismItem
    {
        return DB::transaction(function () use ($id, $data) {
            $item = DestinationTourismItem::findOrFail($id);

            $item->update([
                'icon'                     => $data['icon'] ?? $item->icon,
                'sort_order'               => $data['sort_order'] ?? $item->sort_order,
                'title_translations'       => $data['title_translations'],
                'description_translations' => $data['description_translations'],
            ]);

            return $item;
        });
    }
}