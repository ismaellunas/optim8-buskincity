<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Qirolab\Theme\Theme;

class ThemeService
{
    public function __construct(
        private SettingService $settingService
    ) {}

    private function renderFontSizes(): string
    {
        return view('theme_options.font_size_sass', $this->settingService->getThemeFontSizes())
            ->render();
    }

    private function renderColors(): string
    {
        return view('theme_options.colors_sass', $this->settingService->getThemeColors())
            ->render();
    }

    private function renderFonts(): string
    {
        return view('theme_options.font_sass', $this->settingService->getThemeFonts())
            ->render();
    }

    public function generateVariablesSass()
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/sass'),
        ]);

        $disk->put('variables.sass', $this->renderColors());

        $disk->put('styles_after.sass', (
            $this->renderFonts()
            .$this->renderFontSizes()
        ));
    }

    public function generateCss()
    {
        Artisan::call('webpack:theme-sass', [
            'theme' => Theme::active(),
        ]);
    }

    public function storeCssToSettingTable()
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/css')
        ]);

        foreach (config('constants.settings.generate_css') as $key => $fileName) {

            if (! $disk->exists($fileName)) {
                continue;
            }

            $this->settingService->saveKey(
                $key,
                $disk->get($fileName),
                'stored_css'
            );
        }

        $this->settingService->saveGeneratedCssVersion();
    }

    public function clearStorageTheme(): bool
    {
        $file = new Filesystem();

        return $file->cleanDirectory(storage_path('theme'));
    }
}
