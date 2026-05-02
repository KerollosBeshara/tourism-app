<?php

namespace Modules\Geo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Safely find Georgia using ISO
        $countryId = DB::table('countries')->where('iso_code', 'GE')->value('id');

        if (!$countryId) {
            $this->command->error("Georgia not found. Seed countries first!");
            return;
        }

        $cities = [
            // Major Cities
            ['name' => 'Tbilisi', 'ar' => 'تبليسي', 'slug' => 'tbilisi', 'lat' => 41.7151, 'lon' => 44.8271],
            ['name' => 'Batumi', 'ar' => 'باتومي', 'slug' => 'batumi', 'lat' => 41.6168, 'lon' => 41.6367],
            ['name' => 'Kutaisi', 'ar' => 'كوتيسي', 'slug' => 'kutaisi', 'lat' => 42.2662, 'lon' => 42.7180],
            ['name' => 'Rustavi', 'ar' => 'روستافي', 'slug' => 'rustavi', 'lat' => 41.5472, 'lon' => 44.9933],
            
            // Adjara & Guria (Coastline)
            ['name' => 'Kobuleti', 'ar' => 'كوبوليتي', 'slug' => 'kobuleti', 'lat' => 41.8111, 'lon' => 41.7753],
            ['name' => 'Poti', 'ar' => 'بوتي', 'slug' => 'poti', 'lat' => 42.1468, 'lon' => 41.6720],
            ['name' => 'Ozurgeti', 'ar' => 'أوزورجيتي', 'slug' => 'ozurgeti', 'lat' => 41.9269, 'lon' => 41.9958],

            // Samtskhe-Javakheti (Wellness & Winter)
            ['name' => 'Borjomi', 'ar' => 'بورجومي', 'slug' => 'borjomi', 'lat' => 41.8389, 'lon' => 43.3792],
            ['name' => 'Bakuriani', 'ar' => 'باكورياني', 'slug' => 'bakuriani', 'lat' => 41.7501, 'lon' => 43.5330],
            ['name' => 'Akhaltsikhe', 'ar' => 'أخالتسيخ', 'slug' => 'akhaltsikhe', 'lat' => 41.6389, 'lon' => 42.9861],

            // Kakheti (Wine Region)
            ['name' => 'Telavi', 'ar' => 'تيلافي', 'slug' => 'telavi', 'lat' => 41.9192, 'lon' => 45.4731],
            ['name' => 'Sighnaghi', 'ar' => 'سيغناغي', 'slug' => 'sighnaghi', 'lat' => 41.6200, 'lon' => 45.9217],
            ['name' => 'Kvareli', 'ar' => 'كفاريلي', 'slug' => 'kvareli', 'lat' => 41.9415, 'lon' => 45.8131],
            ['name' => 'Sagarejo', 'ar' => 'ساجاريجو', 'slug' => 'sagarejo', 'lat' => 41.7333, 'lon' => 45.3333],

            // Mtskheta-Mtianeti (History & Ski)
            ['name' => 'Mtskheta', 'ar' => 'متسخيتا', 'slug' => 'mtskheta', 'lat' => 41.8411, 'lon' => 44.7164],
            ['name' => 'Gudauri', 'ar' => 'غودوري', 'slug' => 'gudauri', 'lat' => 42.4764, 'lon' => 44.4800],
            ['name' => 'Stepantsminda', 'ar' => 'ستيبانتسميندا', 'slug' => 'kazbegi', 'lat' => 42.6567, 'lon' => 44.6433],

            // Samegrelo & Svaneti (Mountains)
            ['name' => 'Zugdidi', 'ar' => 'زوغديدي', 'slug' => 'zugdidi', 'lat' => 42.5088, 'lon' => 41.8709],
            ['name' => 'Mestia', 'ar' => 'ميستيا', 'slug' => 'mestia', 'lat' => 43.0451, 'lon' => 42.7278],

            // Shida Kartli & Imereti
            ['name' => 'Gori', 'ar' => 'غوري', 'slug' => 'gori', 'lat' => 41.9842, 'lon' => 44.1158],
            ['name' => 'Zestafoni', 'ar' => 'زيستافوني', 'slug' => 'zestafoni', 'lat' => 42.1103, 'lon' => 43.0339],
            ['name' => 'Samtredia', 'ar' => 'سامتريديا', 'slug' => 'samtredia', 'lat' => 42.1625, 'lon' => 42.3417],
            ['name' => 'Tskaltubo', 'ar' => 'تسكالتوبو', 'slug' => 'tskaltubo', 'lat' => 42.3264, 'lon' => 42.5978],

            // Racha
            ['name' => 'Ambrolauri', 'ar' => 'أمبرولوري', 'slug' => 'ambrolauri', 'lat' => 42.5217, 'lon' => 43.1517],
            ['name' => 'Oni', 'ar' => 'أوني', 'slug' => 'oni', 'lat' => 42.5853, 'lon' => 43.4422],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->updateOrInsert(
                ['slug' => $city['slug'], 'country_id' => $countryId],
                [
                    'id' => (string) Str::ulid(),
                    'name_translations' => json_encode([
                        ['locale' => 'en', 'value' => $city['name']],
                        ['locale' => 'ar', 'value' => $city['ar']],
                    ]),
                    'timezone' => 'Asia/Tbilisi',
                    'latitude' => $city['lat'],
                    'longitude' => $city['lon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}