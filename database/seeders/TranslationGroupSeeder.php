<?php

namespace Database\Seeders;

use App\Entities\Caches\TranslationCache;
use App\Models\Translation;
use App\Services\TranslationManagerService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TranslationGroupSeeder extends Seeder
{
    protected $translationManagerService;
    protected $referenceLocale;

    public function __construct(TranslationManagerService $translationManagerService)
    {
        $this->translationManagerService = $translationManagerService;
        $this->referenceLocale = $this->translationManagerService->getReferenceLocale();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Group - filter
        $translations = [
            [
                'locale' => $this->referenceLocale,
                'group' => 'validation',
                'key' => 'group_and_key_combination',
                'value' => 'The combination between group and key is invalid.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => 'validation',
                'key' => 'current_password',
                'value' => 'The provided password does not match your current password.'
            ],
        ];

        $this->batchCreate($translations);
    }

    protected function batchCreate(array $translations): void
    {
        $this->setTimestampOnTranslations($translations);

        Translation::insert($translations);

        app(TranslationCache::class)->flush();
    }

    protected function setTimestampOnTranslations(array &$translations): void
    {
        foreach ($translations as $index => $translation) {
            $translations[$index]['created_at'] = Carbon::now();
            $translations[$index]['updated_at'] = Carbon::now();
        }
    }
}
