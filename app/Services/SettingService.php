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
    private static function getAdditionalCodeFileKey(string $key): string
    {
        return config("constants.theme_additional_code_files.{$key}.key");
    }

    public function getAdditionalCodeFileName(string $key): string
    {
        return config("constants.theme_additional_code_files.{$key}.filename");
    }

    public static function getFrontendCssUrl(): string
    {
        $urlCss = Setting::where('key', 'url_css')->first(['key', 'value']);

        return $urlCss->value ?? mix('css/app.css')->toHtml();
    }

    public static function getTrackingCodeAfterBodyUrl(): ?string
    {
        return Setting::where('key', self::getAdditionalCodeFileKey('tracking_code_after_body'))
            ->value('value');
    }

    public static function getTrackingCodeBeforeBodyUrl(): ?string
    {
        return Setting::where('key', self::getAdditionalCodeFileKey('tracking_code_before_body'))
            ->value('value');
    }

    public static function getTrackingCodeInsideHeadUrl(): ?string
    {
        return Setting::where('key', self::getAdditionalCodeFileKey('tracking_code_inside_head'))
            ->value('value');
    }

    public static function getAdditionalCssUrl(): ?string
    {
        return Setting::where('key', self::getAdditionalCodeFileKey('additional_css'))
            ->value('value');
    }

    public static function getAdditionalJavascriptUrl(): ?string
    {
        return Setting::where('key', self::getAdditionalCodeFileKey('additional_javascript'))
            ->value('value');
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

    public function getAdditionalCodes(): array
    {
        return Setting::where('group', 'additional_code')
            ->get([
                'display_name',
                'key',
                'value',
                'order',
            ])
            ->keyBy('key')
            ->all();
    }

    public function getFont($key)
    {
        $setting = Setting::where('key', $key)->value('value');
        $font = [];

        if (!is_null($setting)) {
            $font = json_decode($setting, true);
        }

        return (object) array_merge(
            [
                'family' => null,
                'weight' => null,
                'style' => null,
            ],
            $font
        );
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

    public function uploadAdditionalCodeToCloudStorage(
        string $filename,
        string $value,
        string $folderPrefix = null
    ): MediaAsset {

        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/css'),
        ]);

        $disk->put($filename, $value);

        $uploadedFileName = 'theme/css/'.$filename;
        $uploadedFilePath = storage_path($uploadedFileName);

        $file = new UploadedFile(
            $uploadedFilePath,
            $filename
        );

        $storage = new CloudinaryStorage();

        $folder = "assets";

        if ($folderPrefix) {
            $folder = $folderPrefix.'_'.$folder;
        }

        return $storage->upload(
            $file,
            $filename,
            pathinfo($filename, PATHINFO_EXTENSION),
            $folder
        );
    }

    public function saveCssUrl(string $url): bool
    {
        $setting = Setting::firstOrNew(['key' => 'url_css']);
        $setting->value = $url;
        return $setting->save();
    }

    public function saveAdditionalCodeUrl(string $key, string $url): bool
    {
        $setting = Setting::firstOrNew([
            'key' => self::getAdditionalCodeFileKey($key)
        ]);

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
