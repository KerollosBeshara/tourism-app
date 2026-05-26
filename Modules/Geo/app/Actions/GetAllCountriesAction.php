<?php

namespace Modules\Geo\Actions;

use Illuminate\Database\Eloquent\Collection;
use Modules\Geo\Models\Country;

class GetAllCountriesAction
{
    /**
     * Execute the action to retrieve a lightweight collection of all countries.
     *
     * @return Collection
     */
    public function execute(): Collection
    {
        // Only fetch the absolute minimum database columns required for lookup maps
        return Country::select(['id', 'iso_code', 'name_translations'])
            ->orderBy('iso_code', 'asc')
            ->get();
    }
}