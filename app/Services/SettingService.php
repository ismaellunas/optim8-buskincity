<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Entities\CloudinaryStorage;
use App\Entities\MediaAsset;
use App\Helpers\CssUnitConverter;
use App\Helpers\MinifyCss;
use App\Models\{
    Media,
    Setting,
};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\{
    Collection,
    Facades\Artisan,
    Facades\Storage,
    Str,
};
use Qirolab\Theme\Theme;
use Symfony\Component\Process\Process;
use \finfo;

class SettingService
{
    public static function getFrontendCssUrl(): string
    {
        $urlCss = app(SettingCache::class)->remember('url_css', function () {
            return Setting::key('url_css')->value('value') ?? "";
        });

        if (!empty($urlCss)) {
            return $urlCss;
        } else {
            $activeTheme = Theme::active();

            return mix('css/app.css', 'themes/'.$activeTheme)->toHtml();
        }
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
            $media = $this->getLogoMedia();

            return !empty($media) ? $media->file_url : "";
        });
    }

    public function getLogoMedia(): ?Media
    {
        return $this->getMediaFromSetting(
            config("constants.theme_header.header_logo_media.key")
        );
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
            $fontWeight = 'wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700';

            $mainTextFontFamily = $this->getFont('main_text_font')->family ?? null;
            $headingTextFontFamily = $this->getFont('headings_font')->family ?? null;
            $buttonsFontFamily = $this->getFont('buttons_font')->family  ?? null;

            $fontTemplate = (
                '{fontUrl}?family={fontFamily}:ital,{fontWeight}'.
                '&display=swap'
            );
            $fontParams = [
                '{fontUrl}' => $baseGoogleUrlFont,
                '{fontWeight}' => $fontWeight,
            ];

            return [
                'mainTextFont' => (
                    $mainTextFontFamily !== null
                    ? strtr($fontTemplate, array_merge(
                        $fontParams, ['{fontFamily}' => $mainTextFontFamily]
                    ))
                    : ''
                ),
                'headingsFont' => (
                    $headingTextFontFamily !== null
                    ? strtr($fontTemplate, array_merge(
                        $fontParams, ['{fontFamily}' => $headingTextFontFamily]
                    ))
                    : ''
                ),
                'buttonsFont' => (
                    $buttonsFontFamily !== null
                    ? strtr($fontTemplate, array_merge(
                        $fontParams, ['{fontFamily}' => $buttonsFontFamily]
                    ))
                    : ''
                ),
            ];
        });
    }

    private function renderFontSizes(): string
    {
        $settings = Setting::where('group', 'font_size')
                ->get(['key', 'value'])
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => CssUnitConverter::pxToEm($setting->value ?? 0)];
                })
                ->all();

        return view('theme_options.font_size_sass', array_merge(
            config('constants.theme_font_sizes'),
            $settings
        ))->render();
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

        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/sass'),
        ]);

        $disk->put('variables.sass', $variablesSass);

        $stylesAfterApp = view('theme_options.font_sass',
            Setting::where('group', 'font')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->map(function($value) {
                    return json_decode($value);
                })
                ->all()
        )->render();

        $stylesAfterApp .= $this->renderFontSizes();

        $disk->put('styles_after.sass', $stylesAfterApp);
    }

    public function getHomePage()
    {
        return app(SettingCache::class)->remember('home_page', function () {
            return Setting::key('home_page')->value('value') ?? "";
        });
    }

    public function qrCodePublicPageIsDisplayed(): bool
    {
        return app(SettingCache::class)->remember(
            'qrcode_public_page_is_displayed',
            function () {
                $isDisplayed = Setting::key('qrcode_public_page_is_displayed')
                    ->value('value')
                    ?? "";

                return filter_var($isDisplayed, FILTER_VALIDATE_BOOLEAN);
            }
        );
    }

    public function qrCodePublicPageLogo(): string
    {
        return app(SettingCache::class)->remember(
            'qrcode_public_page_logo',
            function () {
                $media = $this->getQrCodePublicPageLogoMedia();

                if ($media) {
                    return $media->file_url;
                }

                return "";
            }
        );
    }

    public function getQrCodePublicPageLogoMedia(): ?Media
    {
        return $this->getMediaFromSetting('qrcode_public_page_logo_media_id');
    }

    public function getFaviconUrl(): string
    {
        return app(SettingCache::class)->remember(
            'favicon_url',
            function () {
                $media = $this->getFaviconMedia();

                if ($media) {
                    return $media->file_url;
                }

                return "";
            }
        );
    }

    public function getFaviconMedia(): ?Media
    {
        return $this->getMediaFromSetting('favicon_media_id');
    }

    private function getMediaFromSetting(string $key): ?Media
    {
        $mediaId = Setting::key($key)
            ->value('value');

        return $mediaId ? Media::find($mediaId) : null;
    }

    public function generateThemeCss()
    {
        $activeTheme = Theme::active();

        Artisan::call('webpack:theme-sass', [
            'theme' => $activeTheme,
            '--change_dir' => '..'
        ]);
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

    public function getSocialiteDrivers(): ?array
    {
        $drivers = Setting::key('socialite_drivers')->value('value');

        return is_null($drivers) ? null : json_decode($drivers);
    }
}
