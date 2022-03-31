<?php

namespace Database\Seeders;

use App\Models\Translation;
use App\Services\TranslationManagerService;
use Illuminate\Database\Seeder;

class TranslationStringSeeder extends Seeder
{
    private $translationManagerService;

    public function __construct(TranslationManagerService $translationManagerService)
    {
        $this->translationManagerService = $translationManagerService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $translation = new Translation;

        $translation->saveFromInputs([
            'locale' => $this->translationManagerService->getReferenceLocale(),
            'key' => 'I love programming.',
            'value' => 'I love programming.'
        ]);
    }
}
