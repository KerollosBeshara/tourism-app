<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\Destination;

class UpsertDestinationAction
{
    public function execute(array $data, ?string $id = null): Destination
    {
        return Destination::updateOrCreate(['id' => $id ?? ($data['id'] ?? null)], $data);
    }
}