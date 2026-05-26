<?php

namespace Modules\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Geo\Models\DestinationTourismItem;

class DeleteDestinationTourismItemsAction
{
    /**
     * Terminate individual asset references cleanly within transactional guardrails.
     */
    public function execute(string $id): void
    {
        DB::transaction(function () use ($id) {
            DestinationTourismItem::findOrFail($id)->delete();
        });
    }
}