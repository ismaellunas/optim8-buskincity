<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Entities\CloudinaryStorage;
use App\Entities\MediaAsset;
use App\Helpers\MinifyCss;
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
    public static function getFrontendCssUrl(): string
    {
        $urlCss = app(SettingCache::class)->remember('url_css', function () {
            return Setting::key('url_css')->value('value') ?? "";
        });

        return !empty($urlCss) ? $urlCss : mix('css/app.css')->toHtml();
    }

    public static function getAdditionalCss(): string
    {
        return app(SettingCache::class)->remember('additional_css', function () {
            return MinifyCss::minify(Setting::key('additional_css')->value('value') ?? "");
        });
    }

    public static function getAdditionalJavascript(): string
    {
        return app(SettingCache::class)->remember('additional_javascript', function () {
            return Setting::key('additional_javascript')->value('value') ?? "";
        });
    }

    public function getTrackingCodeInsideHead(): string
    {
        return app(SettingCache::class)->remember('tracking_code_inside_head', function () {
            return Setting::key('tracking_code_inside_head')->value('value') ?? "";
        });
    }

    public function getTrackingCodeAfterBody(): string
    {
        return app(SettingCache::class)->remember('tracking_code_after_body', function () {
            return Setting::key('tracking_code_after_body')->value('value') ?? "";
        });
    }

    public function getTrackingCodeBeforeBody(): string
    {
        return app(SettingCache::class)->remember('tracking_code_before_body', function () {
            return Setting::key('tracking_code_before_body')->value('value') ?? "";
        });
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
        return app(SettingCache::class)->remember('logo_url', function () {
            $mediaId = Setting::key(config("constants.theme_header.header_logo_media.key"))
                ->value('value');

            return Media::where('id', $mediaId)->value('file_url') ?? "";
        });
    }

    public function getHeaderLayout(): int
    {
        return app(SettingCache::class)->remember('header_layout', function () {
            return (int) Setting::key('header_layout')->value('value');
        });
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

    public function getFontUrls(): array
    {
        return app(SettingCache::class)->remember('fonts', function () {
            $baseGoogleUrlFont = 'https://fonts.googleapis.com/css2';
            $mainTextFontFamily = $this->getFont('main_text_font')->family ?? null;
            $headingTextFontFamily = $this->getFont('headings_font')->family ?? null;
            $buttonsFontFamily = $this->getFont('buttons_font')->family  ?? null;
            return [
                'mainTextFont' => $mainTextFontFamily !== null  ? $baseGoogleUrlFont . '?family=' . $mainTextFontFamily . ':ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap' : '',
                'headingsFont' =>  $headingTextFontFamily !== null ? $baseGoogleUrlFont . '?family=' . $headingTextFontFamily . ':ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap' : '',
                'buttonsFont' => $buttonsFontFamily ? $baseGoogleUrlFont . '?family=' . $buttonsFontFamily . ':ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap' : '',
            ];
        });
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

        $variablesSass .= view('theme_options.font_sass',
            Setting::where('group', 'font')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->map(function($value) {
                    return json_decode($value);
                })
                ->all()
        );

        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/sass'),
        ]);

        $disk->put('biz_variables.sass', $variablesSass);

        $variablesAfterSass = view('theme_options.font_size_sass', array_merge(
            config('constants.theme_font_sizes'),
            Setting::where('group', 'font_size')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->all()
        ));

        $disk->put('biz_variables_after.sass', $variablesAfterSass);
    }

    public function getHomePage()
    {
        return app(SettingCache::class)->remember('home_page', function () {
            return Setting::key('home_page')->value('value');
        });
    }

    public function generateThemeCss()
    {
        exec('cd .. && npx webpack --config webpack.config.biz.js');
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
