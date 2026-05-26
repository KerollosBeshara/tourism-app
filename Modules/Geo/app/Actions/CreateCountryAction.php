<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\Country;

class CreateCountryAction
{
    public function execute(array $data): Country
    {
        return Country::create($data);
    }
}