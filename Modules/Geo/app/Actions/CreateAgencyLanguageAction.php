<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\AgencyLanguage;

class CreateAgencyLanguageAction
{
    public function execute(array $data): AgencyLanguage
    {
        // If setting as default, unset other defaults for this agency
        if ($data['is_default'] ?? false) {
            AgencyLanguage::where('agency_id', $data['agency_id'])
                ->update(['is_default' => false]);
        }

        return AgencyLanguage::create($data);
    }
}