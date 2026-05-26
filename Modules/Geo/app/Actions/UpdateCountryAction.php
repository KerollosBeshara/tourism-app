<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\Country;

class UpdateCountryAction
{
    public function execute(Country $country, array $data): Country
    {
        $country->update($data);
        return $country;
    }
}