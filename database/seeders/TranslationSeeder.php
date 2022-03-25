<?php

namespace Database\Seeders;

use App\Entities\Caches\TranslationCache;
use App\Models\Translation;
use App\Services\TranslationManagerService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;

class TranslationSeeder extends Seeder
{
    private $app;
    private $files;

    public function __construct(
        Application $app,
        Filesystem $files,
        TranslationManagerService $translationManagerService
    ) {
        $this->app = $app;
        $this->files = $files;
        $this->translationManagerService = $translationManagerService;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(?bool $replace = true, ?bool $onCommand = false)
    {
        $this->importTranslations($replace);

        if (!$onCommand) {
            $translation = new Translation;
            $translation->saveFromInputs([
                'locale' => $this->translationManagerService->getReferenceLocale(),
                'key' => 'I love programming.',
                'value' => 'I love programming.'
            ]);
        }

        app(TranslationCache::class)->flush();
    }

    private function importTranslations($replace = false)
    {
        $base = $this->app['path.lang'];

        foreach ($this->files->directories($base) as $langPath) {
            $locale = basename($langPath);

            foreach ($this->files->allfiles($langPath) as $file) {
                $info = pathinfo($file);
                $group = $info['filename'];

                $subLangPath = str_replace($langPath.DIRECTORY_SEPARATOR, '', $info['dirname']);
                $subLangPath = str_replace(DIRECTORY_SEPARATOR, '/', $subLangPath);
                $langPath = str_replace(DIRECTORY_SEPARATOR, '/', $langPath);

                if ($subLangPath != $langPath) {
                    $group = $subLangPath.'/'.$group;
                }

                $translations = Lang::getLoader()->load($locale, $group);

                if ($translations && is_array($translations)) {
                    foreach (Arr::dot($translations) as $key => $value) {
                        $this
                            ->translationManagerService
                            ->saveTranslation($key, $value, $locale, $group, $replace);
                    }
                }
            }
        }
    }
}
