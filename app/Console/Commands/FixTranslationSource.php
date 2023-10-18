<?php

namespace App\Console\Commands;

use App\Entities\Caches\TranslationCache;
use App\Models\Translation;
use App\Services\ModuleService;
use App\Services\TranslationManagerService;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FixTranslationSource extends Command
{
    protected $signature = 'fix:translation-source '.
        '{mode=new : "new|replace|forceCreate" Mode to store the translation}';

    protected $description = 'Populate source column in translations table with optional mode. '.
        'new (store only new records), '.
        'replace (replace existing records), '.
        'forceCreate (force to create records)';

    private $files;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->importTranslations(lang_path());

        $this->importModuleTranslations();

        $this->importAppIdTranslations();

        app(TranslationCache::class)->flush();

        return Command::SUCCESS;
    }

    private function files(): Filesystem
    {
        if (is_null($this->files)) {
            $this->files = app(Filesystem::class);
        }

        return $this->files;
    }

    private function sourceFromFilename($filename): string
    {
        return Str::replace(base_path().'/', '', $filename);
    }

    private function fixTranslation(
        $locale,
        $key,
        $source,
        $group = null,
        $value = null,
        $module = null,
    ): void {
        $mode = $this->argument('mode');

        if (in_array($mode, ['new', 'replace'])) {
            app(TranslationManagerService::class)->saveTranslation(
                key: $key,
                value: $value,
                locale: $locale,
                group: $group,
                source: $source,
                module: $module,
                replace: ($mode == 'replace' ? true : false)
            );

        } elseif ($mode == 'forceCreate') {

            Translation::insertOrIgnore([
                'key' => $key,
                'value' => $value,
                'locale' => $locale,
                'group' => $group ?? null,
                'source' => $source ?? null,
                'module' => $module ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function importTranslations(string $basePath, string $module = null)
    {
        foreach ($this->files()->directories($basePath) as $langPath) {
            $locale = basename($langPath);

            if (empty($module) && $locale == 'modules') continue;

            foreach ($this->files()->allfiles($langPath) as $idx => $file) {
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

                $source = $this->sourceFromFilename($file);

                if ($translations && is_array($translations)) {
                    foreach (Arr::dot($translations) as $key => $value) {
                        $manipulatedGroup = null;
                        $manipulatedKey = null;

                        if ($module && $group == 'terms') {
                            $manipulatedKey = Str::snake($key);
                            $manipulatedGroup = $module.'_term';
                        }

                        $this->fixTranslation(
                            $locale,
                            $manipulatedKey ?? $key,
                            $source,
                            $manipulatedGroup ?? $group,
                            $value,
                            $module,
                        );
                    }
                }
            }
        }

        foreach ($this->files()->files($basePath) as $jsonTranslationFile) {
            if (strpos($jsonTranslationFile, '.json') === false) {
                continue;
            }

            $locale = basename($jsonTranslationFile, '.json');

            $translations = file_get_contents($jsonTranslationFile);
            $translations = json_decode($translations, true);

            $source = $this->sourceFromFilename($jsonTranslationFile);

            if ($translations && is_array($translations)) {
                foreach ($translations as $key => $value) {
                    if (!Str::startsWith($key, '//')) {
                        $this->fixTranslation(
                            $locale,
                            $key,
                            $source,
                            null,
                            $value,
                            $module,
                        );
                    }
                }
            }
        }
    }

    private function importModuleTranslations()
    {
        $modules = app(ModuleService::class)
            ->allModules()
            ->filter(fn ($module) => $module->is_manageable)
            ->pluck('name');

        foreach ($modules as $module) {

            $basePath = lang_path()."/modules/".Str::snake($module);

            if (! is_dir($basePath)) continue;

            $this->importTranslations($basePath, Str::snake($module));
        }
    }

    private function importAppIdTranslations()
    {
        $basePath = lang_path()."/".strtolower(config('app.id'));

        if (is_dir($basePath)) {
            $this->importTranslations($basePath);
        }
    }
}
