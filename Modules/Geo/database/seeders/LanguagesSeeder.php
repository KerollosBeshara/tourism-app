<?php

namespace Modules\Geo\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Geo\Models\Language;
use Illuminate\Support\Str;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds - Most common world languages
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_rtl' => false],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_rtl' => false],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'is_rtl' => false],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'is_rtl' => false],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'is_rtl' => true],
            ['code' => 'bn', 'name' => 'Bengali', 'native_name' => 'বাংলা', 'is_rtl' => false],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'is_rtl' => false],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'is_rtl' => false],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'is_rtl' => false],
            ['code' => 'pa', 'name' => 'Punjabi', 'native_name' => 'ਪੰਜਾਬੀ', 'is_rtl' => false],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'is_rtl' => false],
            ['code' => 'jv', 'name' => 'Javanese', 'native_name' => 'Basa Jawa', 'is_rtl' => false],
            ['code' => 'ms', 'name' => 'Malay', 'native_name' => 'Bahasa Melayu', 'is_rtl' => false],
            ['code' => 'te', 'name' => 'Telugu', 'native_name' => 'తెలుగు', 'is_rtl' => false],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_rtl' => false],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'is_rtl' => false],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_rtl' => false],
            ['code' => 'mr', 'name' => 'Marathi', 'native_name' => 'मराठी', 'is_rtl' => false],
            ['code' => 'ta', 'name' => 'Tamil', 'native_name' => 'தமிழ்', 'is_rtl' => false],
            ['code' => 'ur', 'name' => 'Urdu', 'native_name' => 'اردو', 'is_rtl' => true],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'is_rtl' => false],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'is_rtl' => false],
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย', 'is_rtl' => false],
            ['code' => 'gu', 'name' => 'Gujarati', 'native_name' => 'ગુજરાતી', 'is_rtl' => false],
            ['code' => 'fa', 'name' => 'Persian', 'native_name' => 'فارسی', 'is_rtl' => true],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'is_rtl' => false],
            ['code' => 'ps', 'name' => 'Pashto', 'native_name' => 'پښتو', 'is_rtl' => true],
            ['code' => 'kn', 'name' => 'Kannada', 'native_name' => 'ಕನ್ನಡ', 'is_rtl' => false],
            ['code' => 'ml', 'name' => 'Malayalam', 'native_name' => 'മലയാളം', 'is_rtl' => false],
            ['code' => 'su', 'name' => 'Sundanese', 'native_name' => 'Basa Sunda', 'is_rtl' => false],
            ['code' => 'ha', 'name' => 'Hausa', 'native_name' => 'Hausa', 'is_rtl' => false],
            ['code' => 'or', 'name' => 'Odia', 'native_name' => 'ଓଡ଼ିଆ', 'is_rtl' => false],
            ['code' => 'my', 'name' => 'Burmese', 'native_name' => 'ဗမာစာ', 'is_rtl' => false],
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська', 'is_rtl' => false],
            ['code' => 'tl', 'name' => 'Tagalog', 'native_name' => 'Wikang Tagalog', 'is_rtl' => false],
            ['code' => 'yo', 'name' => 'Yoruba', 'native_name' => 'Yorùbá', 'is_rtl' => false],
            ['code' => 'uz', 'name' => 'Uzbek', 'native_name' => 'Oʻzbek', 'is_rtl' => false],
            ['code' => 'sd', 'name' => 'Sindhi', 'native_name' => 'سنڌي', 'is_rtl' => true],
            ['code' => 'am', 'name' => 'Amharic', 'native_name' => 'አማርኛ', 'is_rtl' => false],
            ['code' => 'ro', 'name' => 'Romanian', 'native_name' => 'Română', 'is_rtl' => false],
            ['code' => 'ig', 'name' => 'Igbo', 'native_name' => 'Asụsụ Igbo', 'is_rtl' => false],
            ['code' => 'az', 'name' => 'Azerbaijani', 'native_name' => 'Azərbaycan dili', 'is_rtl' => false],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'is_rtl' => false],
            ['code' => 'ku', 'name' => 'Kurdish', 'native_name' => 'Kurdî', 'is_rtl' => false],
            ['code' => 'mg', 'name' => 'Malagasy', 'native_name' => 'Malagasy', 'is_rtl' => false],
            ['code' => 'ne', 'name' => 'Nepali', 'native_name' => 'नेपाली', 'is_rtl' => false],
            ['code' => 'si', 'name' => 'Sinhalese', 'native_name' => 'සිංහල', 'is_rtl' => false],
            ['code' => 'km', 'name' => 'Khmer', 'native_name' => 'ខ្មែរ', 'is_rtl' => false],
            ['code' => 'tk', 'name' => 'Turkmen', 'native_name' => 'Türkmen', 'is_rtl' => false],
            ['code' => 'as', 'name' => 'Assamese', 'native_name' => 'অসমীয়া', 'is_rtl' => false],
            ['code' => 'so', 'name' => 'Somali', 'native_name' => 'Af Soomaali', 'is_rtl' => false],
            ['code' => 'hu', 'name' => 'Hungarian', 'native_name' => 'Magyar', 'is_rtl' => false],
            ['code' => 'el', 'name' => 'Greek', 'native_name' => 'Ελληνικά', 'is_rtl' => false],
            ['code' => 'zu', 'name' => 'Zulu', 'native_name' => 'isiZulu', 'is_rtl' => false],
            ['code' => 'cs', 'name' => 'Czech', 'native_name' => 'Čeština', 'is_rtl' => false],
            ['code' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska', 'is_rtl' => false],
            ['code' => 'be', 'name' => 'Belarusian', 'native_name' => 'Беларуская', 'is_rtl' => false],
        ];

        foreach ($languages as $language) {
            Language::create([
                'id' => (string) Str::ulid(),
                'code' => $language['code'],
                'name' => $language['name'],
                'native_name' => $language['native_name'],
                'is_rtl' => $language['is_rtl'],
            ]);
        }
    }
}