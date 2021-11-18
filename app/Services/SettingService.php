<?php

namespace App\Services;

use App\Entities\CloudinaryStorage;
use App\Entities\MediaAsset;
use App\Models\{
    Media,
    Setting,
};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\{
    Collection,
    Facades\Storage,
    Str,
};
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

    private function getSettingsByGroup(string $groupName): Collection
    {
        return Setting::group($groupName)
            ->get([
                'display_name',
                'key',
                'value',
                'order',
            ]);
    }

    public function getColors(): array
    {
        return $this->getSettingsByGroup('theme_color')->keyBy('key')->all();
    }

    public function getFontSizes(): array
    {
        return $this->getSettingsByGroup('font_size')->keyBy('key')->all();
    }

    public function getHeader(): array
    {
        return Setting::where('group', 'header')
            ->get([
                'key',
                'value',
            ])
            ->keyBy('key')
            ->all();
    }

    public function getFooter(): array
    {
        return Setting::where('group', 'footer')
            ->get([
                'key',
                'value',
            ])
            ->keyBy('key')
            ->all();
    }

    public function getLogoUrl(): string
    {
        $setting = Setting::where('key', config("constants.theme_header.header_logo_media.key"))
            ->first();

        $media = Media::select([
                'id',
                'file_url',
            ])
            ->where('id', $setting->value)
            ->first();

        return $media ? $media->file_url : "";
    }

    public function getTrackingCodes(): array
    {
        return $this->getSettingsByGroup('tracking_code')->keyBy('key')->all();
    }

    public function getAdditionalCodes(): array
    {
        return $this->getSettingsByGroup('additional_code')->keyBy('key')->all();
    }

    public function getUppercaseTextOptions(): array
    {
        $uppercaseTexts = [];

        foreach (config('constants.theme_uppercases') as $uppercaseText) {
            $uppercaseTexts[$uppercaseText] = Str::title(
                Str::replace('_', ' ', $uppercaseText)
            );
        }

        return $uppercaseTexts;
    }

    public function getUppercaseTexts(): array
    {
        $uppercaseText = Setting::where('key', 'uppercase_text')->value('value');

        if (!is_null($uppercaseText)) {
            return json_decode($uppercaseText);
        }

        return [];
    }

    public function getContentParagraphWidth(): int
    {
        $contentParagraphWidth = Setting::where('key', 'content_paragraph_width')->value('value');

        return !is_null($contentParagraphWidth)
            ? (int) $contentParagraphWidth
            : config('constants.theme_content_paragraph_width');
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

    public function saveAdditionalCodeUrl(string $key, ?string $url): bool
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

    public function uploadLogoToCloudStorage(
        array $inputs,
        string $folderPrefix = null
    ): MediaAsset {
        $storage = new CloudinaryStorage();


        $folder = "settings";

        if ($folderPrefix) {
            $folder = $folderPrefix.'_'.$folder;
        }

        $this->deleteLogoOnCloudStorage($folder.'/'.$inputs['file_name']);

        return $storage->upload(
            $inputs['file'],
            $inputs['file_name'],
            $inputs['file_type'],
            $folder,
            true,
        );
    }

    public function deleteLogoOnCloudStorage(
        string $fileName
    ) {
        $storage = new CloudinaryStorage();

        $storage->destroy($fileName);
    }
}
