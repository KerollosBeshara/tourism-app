<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\City;

class UpsertCityAction
{
    public function execute(array $data, ?string $id = null): City
    {
        return City::updateOrCreate(['id' => $id ?? ($data['id'] ?? null)], $data);
    }
}