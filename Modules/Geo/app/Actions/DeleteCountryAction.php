<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\Country;

class DeleteCountryAction
{
    public function execute(Country $country): bool
    {
        return $country->delete();
    }
}