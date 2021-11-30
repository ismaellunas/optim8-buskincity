<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;

class TranslationSeeder extends Seeder
{
    private $app;
    private $files;

    public function __construct(Application $app, Filesystem $files)
    {
        $this->app = $app;
        $this->files = $files;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set true if data will be replace existing translations
        // Set false if data will be append new translations
        $this->importTranslations(true);
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
                        $this->saveTranslation($key, $value, $locale, $group, $replace);
                    }
                }
            }
        }
    }

    private function saveTranslation(
        string $key,
        string $value,
        string $locale,
        string $group,
        bool $replace = false
    ) {
        $translation = Translation::firstOrNew([
            'locale' => $locale,
            'group'  => $group,
            'key'    => $key,
        ]);

        // Only replace when empty, or explicitly told so
        if ($replace || ! $translation->value) {
            $translation->value = $value;
        }

        $translation->save();
    }
}
