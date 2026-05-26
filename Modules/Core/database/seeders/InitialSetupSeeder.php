<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Models\User;

class InitialSetupSeeder extends Seeder
{
    public function run(): void
    {
        
        $countryId = DB::table('countries')
            ->where('iso_code', 'GE') // This is the safest way to avoid column name issues
            ->value('id');


        $currencyId = DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        $statusId = DB::table('agency_statuses')
            ->whereRaw("name_translations @> ?", [json_encode([['locale' => 'en', 'value' => 'Pending Review']])])
            ->value('id');


        // Generate IDs up front to link them
        $accountId = (string) Str::ulid();
        $agencyId = (string) Str::ulid();
        $userId = (string) Str::ulid();

        // 2. Create the Account (The Auth Identity)
        DB::table('accounts')->insert([
            'id' => $accountId,
            'email' => 'owner@example.com',
            'password_hash' => Hash::make('password'),
            'is_super_admin' => true,
            'last_login_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create the Agency (The Business Entity)
        DB::table('agencies')->insert([
            'id' => $agencyId,
            'agency_status_id' => $statusId,
            'country_id' => $countryId,
            'base_currency_id' => $currencyId,
            'name' => 'Test Agency',
            'slug' => 'test-agency',
            'contact_email' => 'agency@example.com',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create the User (The Profile linking Account and Agency)
        User::create([
            'id' => $userId,
            'account_id' => $accountId,
            'agency_id' => $agencyId,
            'full_name' => 'Test User',
            'base_role' => 'owner',
            'is_active' => true,
        ]);
    }
}