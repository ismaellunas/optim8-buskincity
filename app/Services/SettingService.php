<?php

namespace App\Services;

use App\Entities\CloudinaryStorage;
use App\Entities\MediaAsset;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use \finfo;

class SettingService
{
    private static function getThemeAdditionalCodeFileKey(string $key): string
    {
        return config("constants.theme_additional_code_files.{$key}.key");
    }

    public static function getFrontendCssUrl(): string
    {
        $urlCss = Setting::where('key', 'url_css')->first(['key', 'value']);

        return $urlCss->value ?? mix('css/app.css')->toHtml();
    }

    public function getColors(): array
    {
        return Setting::where('group', 'theme_color')
            ->get([
                'display_name',
                'key',
                'value',
                'order',
            ])
            ->keyBy('key')
            ->all();
    }

    public function getFontSizes(): array
    {
        return Setting::where('group', 'font_size')
            ->get([
                'display_name',
                'key',
                'value',
                'order',
            ])
            ->keyBy('key')
            ->all();
    }

    public function generateVariablesSass()
    {
        $variablesSass = view('theme_options.colors_sass', array_merge(
            config('constants.theme_colors'),
            Setting::where('group', 'theme_color')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->all()
        ));

        $variablesSass .= view('theme_options.font_size_sass', array_merge(
            config('constants.theme_colors'),
            Setting::where('group', 'font_size')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->all()
        ));

        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/sass'),
        ]);

        $disk->put('sdb_variables.sass', $variablesSass);

        $variablesAfterSass = view('theme_options.font_size_sass', array_merge(
            config('constants.theme_font_sizes'),
            Setting::where('group', 'font_size')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->all()
        ));

        $disk->put('sdb_variables_after.sass', $variablesAfterSass);
    }

    public function generateThemeCss()
    {
        exec('cd .. && npx webpack --config webpack.config.sdb.js');
    }

    public function uploadThemeCssToCloudStorage(string $folderPrefix = null): MediaAsset
    {
        $file_name = 'theme/css/app.css';
        $file_path = storage_path($file_name);
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $file = new UploadedFile(
            $file_path,
            $file_name,
            $finfo->file($file_path),
            filesize($file_path),
            0,
            false
        );

        $storage = new CloudinaryStorage();

        $folder = "assets";

        if ($folderPrefix) {
            $folder = $folderPrefix.'_'.$folder;
        }

        return $storage->upload(
            $file,
            "app.css",
            "css",
            $folder
        );
    }

    public function saveCssUrl(string $url): bool
    {
        $setting = Setting::firstOrNew(['key' => 'url_css']);
        $setting->value = $url;
        return $setting->save();
    }

    public function clearStorageTheme(): bool
    {
        $process = new Process([
            'rm',
            '-rf',
            '..'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'theme'
        ]);
        $process->run();
        return $process->isSuccessful();
    }
}
