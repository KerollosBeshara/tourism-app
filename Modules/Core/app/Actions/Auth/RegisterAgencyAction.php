<?php

namespace Modules\Core\Actions\Auth;

use Modules\Core\Models\Account;
use Modules\Core\Models\Agency;
use Modules\Core\Models\AgencyStatus;
use Modules\Geo\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterAgencyAction
{
    public function execute(array $data): Account
    {
        // Wrap in a transaction so we don't get "orphaned" records if one step fails
        return DB::transaction(function () use ($data) {
            
            // Get the "Pending Review" agency status
            $pendingReviewStatus = AgencyStatus::where('name_translations', 'like', '%Pending Review%')->first();
            
            // If no pending review status found, get the first active status as fallback
            if (!$pendingReviewStatus) {
                $pendingReviewStatus = AgencyStatus::active()->first();
            }
            
            // Get the USD currency by default if not provided
            $baseCurrencyId = $data['base_currency_id'] ?? Currency::byCode('USD')->first()?->id;
            
            // 1. Create the Agency (The Tenant)
            $agency = Agency::create([
                'id'    => (string) Str::ulid(),
                'name'  => $data['agency_name'],
                'slug'  => Str::slug($data['agency_name']),
                'contact_email' => $data['email'],
                'agency_status_id' => $pendingReviewStatus?->id,
                'country_id' => $data['country_id'],
                'base_currency_id' => $baseCurrencyId,
                'is_active' => false,
            ]);

            // 2. Create the Account (The Security/Identity)
            $account = Account::create([
                'id'            => (string) Str::ulid(),
                'email'         => $data['email'],
                'password_hash' => Hash::make($data['password']),
            ]);

            // 3. Create the User Profile (The Owner linking Account to Agency)
            $account->users()->create([
                'id'         => (string) Str::ulid(),
                'account_id' => $account->id,
                'agency_id'  => $agency->id,
                'full_name'  => $data['full_name'],
                'base_role'  => 'owner', // The Agent who registered
            ]);

            return $account;
        });
    }
}