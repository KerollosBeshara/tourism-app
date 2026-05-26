<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\AgencyLanguage;
use Illuminate\Support\Collection;

class DeleteAgencyLanguageAction
{
    public function execute(Collection $ids): int
    {
        // For bulk delete, return the number of deleted records
        return AgencyLanguage::whereIn('id', $ids)->delete();
    }
}