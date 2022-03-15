<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Database\Seeder;

class LanguageSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->setActiveLanguages();
        $this->setDefaultLanguage();
    }

    private function setActiveLanguages(): void
    {
        $activeLanguages = [
            'en',
            'pt',
            'sv',
            'de',
        ];

        Language::whereIn('code', $activeLanguages)
            ->update(['is_active' => true]);
    }

    private function setDefaultLanguage(): void
    {
        $englishId = Language::where('code', 'en')->value('id');

        app(LanguageService::class)->setDefault($englishId);
    }
}
