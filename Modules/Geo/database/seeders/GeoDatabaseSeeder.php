<?php

namespace Modules\Geo\Database\Seeders;

use Illuminate\Database\Seeder;

class GeoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CountriesSeed::class,
            CurrenciesSeeder::class,
            LanguagesSeeder::class,
            DestinationSeeder::class,
        ]);
    }
}

