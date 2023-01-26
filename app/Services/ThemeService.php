<?php

namespace App\Services;

use App\Entities\CloudinaryStorage;
use App\Entities\MediaAsset;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Qirolab\Theme\Theme;
use \finfo;

class ThemeService
{
    private $settingService;

    private $cssFilenameFrontend = 'app';
    private $cssFilenameBackend  = 'app_backend';

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

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

    private function uploadCssToCloudStorage(string $cssFileName): MediaAsset
    {
        $fileName = "theme/css/$cssFileName.css";
        $filePath = storage_path($fileName);
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $file = new UploadedFile(
            $filePath,
            $fileName,
            $finfo->file($filePath),
            filesize($filePath),
            0,
            false
        );

        $storage = new CloudinaryStorage();

        $folder = config('filesystem.folder_prefix')."assets";

        return $storage->upload(
            $file,
            "$cssFileName.css",
            "css",
            $folder
        );
    }

    public function uploadCssFrontend(): MediaAsset
    {
        return $this->uploadCssToCloudStorage($this->cssFilenameFrontend);
    }

    public function uploadCssBackend(): MediaAsset
    {
        return $this->uploadCssToCloudStorage($this->cssFilenameBackend);
    }

    public function clearStorageTheme(): bool
    {
        $file = new Filesystem();

        return $file->cleanDirectory(storage_path('theme'));
    }
}
