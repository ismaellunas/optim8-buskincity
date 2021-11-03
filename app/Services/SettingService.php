<?php

namespace App\Services;

use App\Entities\CloudinaryStorage;
use App\Entities\MediaAsset;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;
use \finfo;

class SettingService
{
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

    public function getHeader(): array
    {
        return Setting::where('group', 'header')
            ->get([
                'key',
                'value',
                'updated_at',
            ])
            ->keyBy('key')
            ->all();
    }

    public function getFooters(): array
    {
        return Setting::where('group', 'footer')
            ->get([
                'key',
                'value',
                'updated_at',
            ])
            ->keyBy('key')
            ->all();
    }

    public function getHeaderLayoutLastSaved()
    {
        $settings = $this->getHeader();
        $headerLayout = $settings['header_layout'];

        if ($headerLayout) {
            return Carbon::parse($headerLayout->updated_at)->format('M d, Y \a\t h:i');
        }

        return '-';
    }

    public function getHeaderLogoUrlLastSaved()
    {
        $settings = $this->getHeader();
        $headerLogo = $settings['header_logo_url'];

        if ($headerLogo) {
            return Carbon::parse($headerLogo->updated_at)->format('M d, Y \a\t h:i');
        }

        return '-';
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

    public function uploadLogoToCloudStorage(array $inputs): MediaAsset
    {
        $storage = new CloudinaryStorage();

        $folder = "assets/logo";

        $this->deleteLogoOnCloudStorage($inputs['file_name']);

        return $storage->upload(
            $inputs['file'],
            $inputs['file_name'],
            $inputs['file_type'],
            $folder
        );
    }

    public function deleteLogoOnCloudStorage(
        string $fileName = 'logo'
    ) {
        $storage = new CloudinaryStorage();
        $fileName = 'assets/logo/'.$fileName;

        $storage->destroy($fileName);
    }
}
