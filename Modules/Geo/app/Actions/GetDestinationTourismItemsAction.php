<?php

namespace Modules\Geo\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Geo\Models\Destination;

class GetDestinationTourismItemsAction
{
    /**
     * Retrieve a paginated list of tourism items ordered sequentially.
     */
    public function execute(string $destinationId): LengthAwarePaginator
    {
        return Destination::findOrFail($destinationId)
            ->tourismItems()
            ->orderBy('sort_order', 'asc')
            ->paginate(15);
    }
}