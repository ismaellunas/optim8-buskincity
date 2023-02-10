<?php

namespace Database\Seeders;

use App\Entities\Caches\TranslationCache;
use App\Services\TranslationManagerService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\{
    Arr,
    Str
};
use App\Services\ModuleService;

class TranslationSeeder extends Seeder
{
    private $app;
    private $files;
    private $translationManagerService;

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
    public function run(?bool $replace = true)
    {
        $this->importTranslations($this->app['path.lang'], $replace);

        $this->importModuleTranslations($replace);

        app(TranslationCache::class)->flush();
    }

    private function sourceFromFilename($filename): string
    {
        return Str::replace($this->app['path.base'].'/', '', $filename);
    }

    private function importTranslations(string $basePath, $replace = false)
    {
        foreach ($this->files->directories($basePath) as $langPath) {
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

                if (! Str::endsWith($file, '.php')) {
                    continue;
                }

                $translations = require $file;

                if ($translations && is_array($translations)) {
                    foreach (Arr::dot($translations) as $key => $value) {
                        $this->translationManagerService->saveTranslation(
                            $key,
                            $value,
                            $locale,
                            $group,
                            $replace,
                            $this->sourceFromFilename($file)
                        );
                    }
                }
            }
        }

        foreach ($this->files->files($basePath) as $jsonTranslationFile) {
            if (strpos($jsonTranslationFile, '.json') === false) {
                continue;
            }

            $locale = basename($jsonTranslationFile, '.json');

            $translations = file_get_contents($jsonTranslationFile);
            $translations = json_decode($translations, true);

            if ($translations && is_array($translations)) {
                foreach ($translations as $key => $value) {
                    if (!Str::startsWith($key, '//')) {
                        $this->translationManagerService->saveTranslation(
                            $key,
                            $value,
                            $locale,
                            null,
                            $replace,
                            $this->sourceFromFilename($jsonTranslationFile)
                        );
                    }
                }
            }
        }
    }

    private function importModuleTranslations($replace = false)
    {
        foreach (app(ModuleService::class)->getAllEnabledNames() as $module) {

            $basePath = $this->app['path.lang']."/modules/".strtolower($module);

            if (! is_dir($basePath)) continue;

            $this->importTranslations($basePath, $replace);
        }
    }
}
