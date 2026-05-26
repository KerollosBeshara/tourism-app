<?php

namespace Modules\Geo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedGeorgiaDestinations();
        $this->seedArmeniaDestinations();
        $this->seedAzerbaijanDestinations();
    }

    private function seedGeorgiaDestinations(): void
    {
        $countryId = DB::table('countries')->where('iso_code', 'GE')->value('id');
        if (!$countryId) {
            $this->command->error("Georgia not found. Seed countries first!");
            return;
        }

        $destinations = [
            // Tbilisi Region
            [
                'name' => 'Tbilisi',
                'ar' => 'تبليسي',
                'slug' => 'tbilisi-georgia',
                'lat' => 41.7151,
                'lon' => 44.8271,
                'desc' => 'The vibrant capital city of Georgia, known for its unique blend of ancient and modern architecture, wine culture, and warm hospitality.',
                'ar_desc' => 'عاصمة جورجيا النابضة بالحياة، معروفة بمزجها الفريد بين العمارة القديمة والحديثة وثقافة النبيذ والضيافة الدافئة.',
            ],
            [
                'name' => 'Mtskheta',
                'ar' => 'متسخيتا',
                'slug' => 'mtskheta',
                'lat' => 41.8411,
                'lon' => 44.7164,
                'desc' => 'Ancient capital of Georgia, home to the Svetitskhovloba Cathedral, a UNESCO World Heritage Site showcasing Georgian Orthodox heritage.',
                'ar_desc' => 'عاصمة جورجيا القديمة، موطن كاتدرائية سفيتيتسخوفيلوبا، موقع تراث عالمي لليونسكو يعرض التراث الأرثوذكسي الجورجي.',
            ],

            // Mountain Regions
            [
                'name' => 'Kazbegi',
                'ar' => 'كازبيغي',
                'slug' => 'kazbegi-georgia',
                'lat' => 42.6567,
                'lon' => 44.6433,
                'desc' => 'Mountain adventure destination featuring Mount Kazbek, pristine nature, and the iconic Tetrami Monastery perched on a mountain peak.',
                'ar_desc' => 'وجهة مغامرة جبلية تتميز بجبل كازبيك والطبيعة البكر وديرت يتاترامي الشهير على قمة الجبل.',
            ],
            [
                'name' => 'Mestia',
                'ar' => 'ميستيا',
                'slug' => 'mestia-svaneti',
                'lat' => 43.0451,
                'lon' => 42.7278,
                'desc' => 'Gateway to Svaneti mountains with traditional stone towers, high-altitude trekking, and authentic Caucasian culture.',
                'ar_desc' => 'بوابة جبال سفانيتي مع الأبراج الحجرية التقليدية والرحلات على المرتفعات والثقافة القوقازية الأصلية.',
            ],

            // Wine Region
            [
                'name' => 'Telavi',
                'ar' => 'تيلافي',
                'slug' => 'telavi-wine-region',
                'lat' => 41.9192,
                'lon' => 45.4731,
                'desc' => 'Heart of Kakheti wine region, featuring ancient wine cellars, vineyards, and wine tasting experiences in Georgia\'s premier wine territory.',
                'ar_desc' => 'قلب منطقة نبيذ كاخيتي، تتميز بأقبية النبيذ القديمة والكروم وتجارب تذوق النبيذ في أفضل منطقة نبيذ في جورجيا.',
            ],
            [
                'name' => 'Sighnaghi',
                'ar' => 'سيغناغي',
                'slug' => 'sighnaghi-wine',
                'lat' => 41.6200,
                'lon' => 45.9217,
                'desc' => 'Romantic wine town perched on hilltop with panoramic views, ancient walls, wine shops, and traditional Georgian hospitality.',
                'ar_desc' => 'بلدة نبيذ رومانسية على قمة التل مع إطلالات بانورامية وجدران قديمة ومتاجر النبيذ والضيافة الجورجية التقليدية.',
            ],

            // Coastal Region
            [
                'name' => 'Batumi',
                'ar' => 'باتومي',
                'slug' => 'batumi-black-sea',
                'lat' => 41.6368,
                'lon' => 41.6450,
                'desc' => 'Black Sea resort city with beautiful beaches, botanical gardens, and vibrant nightlife, Georgia\'s main beach destination.',
                'ar_desc' => 'مدينة منتجع بحر أسود مع شواطئ جميلة وحدائق نباتية وحياة ليلية نابضة بالحياة، وجهة الشواطئ الرئيسية في جورجيا.',
            ],
            [
                'name' => 'Sarpi',
                'ar' => 'سارپي',
                'slug' => 'sarpi-beach',
                'lat' => 41.9369,
                'lon' => 41.3900,
                'desc' => 'Scenic beach village where river meets sea, known for fresh seafood, unique landscape, and peaceful coastal atmosphere.',
                'ar_desc' => 'قرية شاطئية خلابة حيث يلتقي النهر بالبحر، معروفة بالمأكولات البحرية الطازجة والمناظر الطبيعية الفريدة والأجواء الساحلية الهادئة.',
            ],

            // Wellness Region
            [
                'name' => 'Borjomi',
                'ar' => 'بورجومي',
                'slug' => 'borjomi-springs',
                'lat' => 41.8389,
                'lon' => 43.3792,
                'desc' => 'Famous mineral springs and spa destination with pine forests, ideal for wellness retreats and relaxation in nature.',
                'ar_desc' => 'وجهة ينابيع معدنية وسبا مشهورة مع غابات الصنوبر، مثالية لعطل الصحة والاسترخاء في الطبيعة.',
            ],
            [
                'name' => 'Bakuriani',
                'ar' => 'باكورياني',
                'slug' => 'bakuriani-ski',
                'lat' => 41.7501,
                'lon' => 43.5330,
                'desc' => 'Winter sports paradise with ski slopes, summer hiking trails, and high-altitude mountain experiences.',
                'ar_desc' => 'جنة الرياضات الشتوية مع منحدرات التزلج وممرات المشي لمسافات طويلة الصيفية وتجارب الجبال على ارتفاع عالي.',
            ],

            // Historical Regions
            [
                'name' => 'Gori',
                'ar' => 'غوري',
                'slug' => 'gori-georgia',
                'lat' => 41.9842,
                'lon' => 44.1158,
                'desc' => 'Historic town with ancient fortress ruins and museums, gateway to important Georgian historical sites.',
                'ar_desc' => 'بلدة تاريخية مع آثار القلعة القديمة والمتاحف، بوابة المواقع التاريخية الجورجية المهمة.',
            ],
            [
                'name' => 'Akhaltsikhe',
                'ar' => 'أخالتسيخ',
                'slug' => 'akhaltsikhe',
                'lat' => 41.6389,
                'lon' => 42.9861,
                'desc' => 'Medieval fortress town with restored ancient citadel, showcasing Georgian architecture and history.',
                'ar_desc' => 'بلدة حصن وسيطة مع حصن قديم مرمم، يعرض العمارة والتاريخ الجورجيين.',
            ],
        ];

        foreach ($destinations as $dest) {
            DB::table('destinations')->updateOrInsert(
                ['slug' => $dest['slug'], 'country_id' => $countryId],
                [
                    'id' => (string) Str::ulid(),
                    'country_id' => $countryId,
                    'name_translations' => json_encode([
                        ['locale' => 'en', 'value' => $dest['name']],
                        ['locale' => 'ar', 'value' => $dest['ar']],
                    ]),
                    'slug' => $dest['slug'],
                    'timezone' => 'Asia/Tbilisi',
                    'latitude' => $dest['lat'],
                    'longitude' => $dest['lon'],
                    'meta' => json_encode([
                        'description_en' => $dest['desc'],
                        'description_ar' => $dest['ar_desc'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✓ Georgia destinations seeded successfully');
    }

    private function seedArmeniaDestinations(): void
    {
        $countryId = DB::table('countries')->where('iso_code', 'AM')->value('id');
        if (!$countryId) {
            $this->command->warn("Armenia not found. Skipping Armenia destinations.");
            return;
        }

        $destinations = [
            [
                'name' => 'Yerevan',
                'ar' => 'يريفان',
                'slug' => 'yerevan-armenia',
                'lat' => 40.1697,
                'lon' => 44.5010,
                'desc' => 'Capital of Armenia, home to ancient history, vibrant culture, museums, and stunning views of Mount Ararat.',
                'ar_desc' => 'عاصمة أرمينيا، موطن التاريخ القديم والثقافة النابضة بالحياة والمتاحف والآثار المذهلة لجبل أرارات.',
            ],
            [
                'name' => 'Geghard',
                'ar' => 'جيغارد',
                'slug' => 'geghard-monastery',
                'lat' => 40.1697,
                'lon' => 44.7358,
                'desc' => 'UNESCO World Heritage monastery carved into rock, famous for acoustics and ancient spiritual significance.',
                'ar_desc' => 'دير تراث عالمي لليونسكو منحوت في الصخور، مشهور بالصوتيات والأهمية الروحية القديمة.',
            ],
            [
                'name' => 'Khor Virap',
                'ar' => 'خور فيراب',
                'slug' => 'khor-virap',
                'lat' => 39.9375,
                'lon' => 44.4764,
                'desc' => 'Ancient monastery with breathtaking views of Mount Ararat, one of Armenia\'s most iconic religious sites.',
                'ar_desc' => 'دير قديم مع إطلالات مذهلة على جبل أرارات، أحد أهم المواقع الدينية في أرمينيا.',
            ],
            [
                'name' => 'Garni',
                'ar' => 'غارني',
                'slug' => 'garni-temple',
                'lat' => 40.1375,
                'lon' => 44.7761,
                'desc' => 'Ancient Armenian temple with Hellenistic architecture, surrounded by scenic gorges and natural beauty.',
                'ar_desc' => 'معبد أرميني قديم بعمارة هيلينية، محاط بالمضايق الخلابة والجمال الطبيعي.',
            ],
            [
                'name' => 'Dilijan',
                'ar' => 'ديليجان',
                'slug' => 'dilijan-armenia',
                'lat' => 40.7608,
                'lon' => 44.9397,
                'desc' => 'Mountain resort town surrounded by forests, known as "Armenian Switzerland" for its natural beauty and outdoor activities.',
                'ar_desc' => 'بلدة منتجع جبلية محاطة بالغابات، تُعرف باسم "سويسرا الأرمينية" لجمالها الطبيعي وأنشطتها الخارجية.',
            ],
            [
                'name' => 'Tatev',
                'ar' => 'تاتيف',
                'slug' => 'tatev-monastery',
                'lat' => 39.4489,
                'lon' => 46.2442,
                'desc' => 'Spectacular monastery perched on cliff, accessible by the world\'s longest reversible aerial tramway.',
                'ar_desc' => 'دير مذهل على منحدر، يمكن الوصول إليه بواسطة أطول سكك جوية عكسية في العالم.',
            ],
            [
                'name' => 'Sevan',
                'ar' => 'سيفان',
                'slug' => 'lake-sevan',
                'lat' => 40.5819,
                'lon' => 45.0896,
                'desc' => 'Largest freshwater lake in the Caucasus with beach resorts, water activities, and scenic mountain surroundings.',
                'ar_desc' => 'أكبر بحيرة مياه عذبة في القوقاز مع منتجعات الشواطئ والأنشطة المائية والمناطق المحيطة الجبلية الخلابة.',
            ],
        ];

        foreach ($destinations as $dest) {
            DB::table('destinations')->updateOrInsert(
                ['slug' => $dest['slug'], 'country_id' => $countryId],
                [
                    'id' => (string) Str::ulid(),
                    'country_id' => $countryId,
                    'name_translations' => json_encode([
                        ['locale' => 'en', 'value' => $dest['name']],
                        ['locale' => 'ar', 'value' => $dest['ar']],
                    ]),
                    'slug' => $dest['slug'],
                    'timezone' => 'Asia/Yerevan',
                    'latitude' => $dest['lat'],
                    'longitude' => $dest['lon'],
                    'meta' => json_encode([
                        'description_en' => $dest['desc'],
                        'description_ar' => $dest['ar_desc'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✓ Armenia destinations seeded successfully');
    }

    private function seedAzerbaijanDestinations(): void
    {
        $countryId = DB::table('countries')->where('iso_code', 'AZ')->value('id');
        if (!$countryId) {
            $this->command->warn("Azerbaijan not found. Skipping Azerbaijan destinations.");
            return;
        }

        $destinations = [
            [
                'name' => 'Baku',
                'ar' => 'باكو',
                'slug' => 'baku-azerbaijan',
                'lat' => 40.3855,
                'lon' => 49.8671,
                'desc' => 'Dynamic capital city on the Caspian Sea, known for modern architecture, Old City UNESCO site, and oil industry heritage.',
                'ar_desc' => 'عاصمة ديناميكية على بحر قزوين، معروفة بالعمارة الحديثة وموقع المدينة القديمة لليونسكو وتراث صناعة النفط.',
            ],
            [
                'name' => 'Lahij',
                'ar' => 'لاهيج',
                'slug' => 'lahij-azerbaijan',
                'lat' => 40.8208,
                'lon' => 48.7211,
                'desc' => 'Ancient mountain village famous for traditional copperware, cascading rivers, and authentic Caucasian craftsmanship.',
                'ar_desc' => 'قرية جبلية قديمة مشهورة بأدوات النحاس التقليدية والأنهار المتدفقة والحرف اليدوية القوقازية الأصلية.',
            ],
            [
                'name' => 'Ganja',
                'ar' => 'غنجة',
                'slug' => 'ganja-azerbaijan',
                'lat' => 40.6833,
                'lon' => 46.3667,
                'desc' => 'Second largest city with historic mosques, bazaars, and gardens, showcasing rich Azerbaijani cultural heritage.',
                'ar_desc' => 'ثاني أكبر مدينة مع مساجد وأسواق وحدائق تاريخية، تعرض التراث الثقافي الأذربيجاني الغني.',
            ],
            [
                'name' => 'Shaki',
                'ar' => 'شاكي',
                'slug' => 'shaki-azerbaijan',
                'lat' => 41.6292,
                'lon' => 47.4925,
                'desc' => 'Historic Silk Road city with stunning Khan\'s Palace, traditional markets, and surrounding mountain landscapes.',
                'ar_desc' => 'مدينة طريق الحرير التاريخية مع قصر خان المذهل والأسواق التقليدية والمناظر الطبيعية الجبلية المحيطة.',
            ],
            [
                'name' => 'Quba',
                'ar' => 'قبة',
                'slug' => 'quba-azerbaijan',
                'lat' => 41.3725,
                'lon' => 48.4758,
                'desc' => 'Mountain resort surrounded by nature, famous for red mud volcanoes, waterfalls, and outdoor adventures.',
                'ar_desc' => 'منتجع جبلي محاط بالطبيعة، مشهور بركاني الطين الأحمر والشلالات والمغامرات الخارجية.',
            ],
            [
                'name' => 'Mud Volcanoes',
                'ar' => 'ركاني الطين',
                'slug' => 'mud-volcanoes',
                'lat' => 40.2500,
                'lon' => 49.1667,
                'desc' => 'Unique geological phenomenon with hundreds of mud volcanoes, creating otherworldly lunar landscape.',
                'ar_desc' => 'ظاهرة جيولوجية فريدة مع مئات البراكين الطينية، مما يخلق مناظر قمر غريبة.',
            ],
            [
                'name' => 'Absheron Peninsula',
                'ar' => 'شبه جزيرة أبشيرون',
                'slug' => 'absheron-peninsula',
                'lat' => 40.5200,
                'lon' => 49.8500,
                'desc' => 'Coastal peninsula with gas fire mountains, Ateshgah fire temple, and pristine Caspian beaches.',
                'ar_desc' => 'شبه جزيرة ساحلية مع جبال النار الغازية ومعبد النار أتيشجاه وشواطئ بحر قزوين البكر.',
            ],
            [
                'name' => 'Gobustan',
                'ar' => 'غوبستان',
                'slug' => 'gobustan-azerbaijan',
                'lat' => 40.4850,
                'lon' => 49.1050,
                'desc' => 'Petroglyphs and ancient rock carvings dating back 5000 years, UNESCO protected archaeological site.',
                'ar_desc' => 'نقوش صخرية وكتابات صخرية قديمة يعود تاريخها إلى 5000 سنة، موقع أثري محمي بموجب اليونسكو.',
            ],
        ];

        foreach ($destinations as $dest) {
            DB::table('destinations')->updateOrInsert(
                ['slug' => $dest['slug'], 'country_id' => $countryId],
                [
                    'id' => (string) Str::ulid(),
                    'country_id' => $countryId,
                    'name_translations' => json_encode([
                        ['locale' => 'en', 'value' => $dest['name']],
                        ['locale' => 'ar', 'value' => $dest['ar']],
                    ]),
                    'slug' => $dest['slug'],
                    'timezone' => 'Asia/Baku',
                    'latitude' => $dest['lat'],
                    'longitude' => $dest['lon'],
                    'meta' => json_encode([
                        'description_en' => $dest['desc'],
                        'description_ar' => $dest['ar_desc'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✓ Azerbaijan destinations seeded successfully');
    }
}
