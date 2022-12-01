<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTestSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            ['Arabic', 'ar', 'ar'],
            ['Chinese (China)', 'zh-cn', 'zh_CN'],
            ['Dutch', 'nl', 'nl_NL'],
            ['English', 'en', 'en_US'],
            ['English (UK)', 'en-gb', 'en_GB'],
            ['German', 'de', 'de_DE'],
            ['Indonesian', 'id', 'id_ID'],
            ['Italian', 'it', 'it_IT'],
            ['Portuguese (Portugal)', 'pt', 'pt_PT'],
            ['Swedish', 'sv', 'sv_SE'],
        ];

        Language::insert(array_map(
            function ($language) {
                return [
                    'name' => $language[0],
                    'code' => $language[1],
                    'locale' => $language[2],
                ];
            },
            $languages
        ));
    }
}
