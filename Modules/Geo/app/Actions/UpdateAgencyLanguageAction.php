<?php

namespace Modules\Geo\Actions;

use Modules\Geo\Models\AgencyLanguage;

class UpdateAgencyLanguageAction
{
    public function execute(AgencyLanguage $agencyLanguage, array $data): AgencyLanguage
    {
        // If setting as default, unset other defaults for this agency
        if ($data['is_default'] ?? false) {
            AgencyLanguage::where('agency_id', $agencyLanguage->agency_id)
                ->where('id', '!=', $agencyLanguage->id)
                ->update(['is_default' => false]);
        }

        $agencyLanguage->update($data);

        return $agencyLanguage->fresh();
    }
}