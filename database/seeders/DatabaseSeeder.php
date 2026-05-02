<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Models\User;

use Modules\Core\Database\Seeders\AgencyStatusSeeder;
use Modules\Geo\Database\Seeders\CountriesSeed;
use Modules\Geo\Database\Seeders\CitySeeder;
use Modules\Geo\Database\Seeders\CurrenciesSeeder;
use Modules\Geo\Database\Seeders\LanguagesSeeder;
use Modules\Core\Database\Seeders\InitialSetupSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AgencyStatusSeeder::class,
            CountriesSeed::class,
            CitySeeder::class,
            CurrenciesSeeder::class,
            LanguagesSeeder::class,
            InitialSetupSeeder::class,
        ]);



        // $accountId = (string) Str::ulid();
        // $statusId = (string) Str::ulid();
        // $countryId = (string) Str::ulid();
        // $currencyId = (string) Str::ulid();
        // $agencyId = (string) Str::ulid();
        // $userId = (string) Str::ulid();

        // DB::table('accounts')->insert([
        //     'id' => $accountId,
        //     'email' => 'owner@example.com',
        //     'password_hash' => Hash::make('password'),
        //     'is_super_admin' => true,
        //     'last_login_at' => now(),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // DB::table('agency_statuses')->insert([
        //     'id' => $statusId,
        //     'name_translations' => json_encode(['en' => 'Active']),
        //     'color_code' => '#22c55e',
        //     'sort_order' => 0,
        //     'is_active' => true,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // DB::table('countries')->insert([
        //     'id' => $countryId,
        //     'iso_code' => 'USA',
        //     'emoji_flag' => '🇺🇸',
        //     'name_translations' => json_encode(['en' => 'United States']),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // DB::table('currencies')->insert([
        //     'id' => $currencyId,
        //     'code' => 'USD',
        //     'symbol' => '$',
        //     'name_translations' => json_encode(['en' => 'US Dollar']),
        //     'decimal_places' => 2,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // DB::table('agencies')->insert([
        //     'id' => $agencyId,
        //     'agency_status_id' => $statusId,
        //     'country_id' => $countryId,
        //     'base_currency_id' => $currencyId,
        //     'name' => 'Test Agency',
        //     'slug' => 'test-agency',
        //     'logo_path' => null,
        //     'brand_color' => '#0ea5e9',
        //     'contact_email' => 'agency@example.com',
        //     'contact_phone' => '+10000000000',
        //     'official_address' => '123 Example St',
        //     'timezone' => 'UTC',
        //     'date_format' => 'DD/MM/YYYY',
        //     'social_links' => json_encode([]),
        //     'account_manager_id' => null,
        //     'is_active' => true,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // User::create([
        //     'id' => $userId,
        //     'account_id' => $accountId,
        //     'agency_id' => $agencyId,
        //     'full_name' => 'Test User',
        //     'base_role' => 'owner',
        //     'is_active' => true,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }
}
