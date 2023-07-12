<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Helpers\CssUnitConverter;
use App\Helpers\MinifyCss;
use App\Models\{
    Page,
    Media,
    Setting,
};
use App\Services\StorageService;
use Illuminate\Support\{
    Carbon,
    Collection,
    Str,
};
use Illuminate\Support\Facades\Vite;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mews\Purifier\Facades\Purifier;

class SettingService
{
    private static function getKey(string $key): string
    {
        return app(SettingCache::class)->remember($key, function () use ($key) {
            return Setting::key($key)->value('value') ?? "";
        });
    }

    public function saveKey(string $key, mixed $value, string $group = null): Setting
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->group = $group;
        $setting->save();

        return $setting;
    }

    private function cssUrl($key, $fallbackUrl): string
    {
        $version = $this->getStoredCssVersion();

        if ($version) {
            return route('css.stored', [
                'css_name' => config('constants.settings.generate_css.'.$key),
                'ver' => $version,
            ]);
        }

        return $fallbackUrl;
    }

    public function getFrontendCssUrl(): string
    {
        return $this->cssUrl(
            'css_app',
            Vite::asset('themes/'.config('theme.active').'/sass/app.sass')
        );
    }

    public function getBackendCssUrl(): string
    {
        return $this->cssUrl(
            'css_app_backend',
            Vite::asset('resources/sass/app.sass')
        );
    }

    public function getEmailCustomizedStyle(): string
    {
        return $this->getKey('css_app_email');
    }

    public static function getAdditionalCss(): string
    {
        return app(SettingCache::class)->remember('additional_css', function () {
            return MinifyCss::minify(Setting::key('additional_css')->value('value') ?? "");
        });
    }

    public function storedCss(string $fileName): ?array
    {
        $cssKeys = array_keys(config('constants.settings.generate_css'), $fileName);

        if (! empty($cssKeys)) {

            $key = $cssKeys[0];

            $lastModified = Carbon::createFromFormat('YmdHis', $this->getStoredCssVersion());

            return [
                'content' => $this->getKey($key),
                'lastModified' => $lastModified,
            ];
        }

        return null;
    }

    public function saveGeneratedCssVersion()
    {
        $this->saveKey('version_css_app', date('YmdHis'), 'stored_css_version');
    }

    public function getStoredCssVersion(): string
    {
        return $this->getKey('version_css_app');
    }

    public function getAdditionalJavascript(): string
    {
        return $this->getKey('additional_javascript');
    }

    public function getTrackingCodeInsideHead(): string
    {
        return $this->getKey('tracking_code_inside_head');
    }

    public function getTrackingCodeAfterBody(): string
    {
        return $this->getKey('tracking_code_after_body');
    }

    public function getTrackingCodeBeforeBody(): string
    {
        return $this->getKey('tracking_code_before_body');
    }

    private function getSettingsByGroup(
        string $groupName,
        bool $isPrefix = false
    ): Collection {
        if ($isPrefix) {
            $query = Setting::groupPrefix($groupName);
        } else {
            $query = Setting::group($groupName);
        }

        $result = $query->get([
                'display_name',
                'key',
                'value',
                'order',
                'group',
            ]);

        $this->transformSetting($result);

        return $result;
    }

    private function transformSetting(Collection $result): void
    {
        $result->transform(function ($item) {
            $item->display_name = __($item->display_name);

            return $item;
        });
    }

    public function getColors(): array
    {
        return $this->getSettingsByGroup('theme_color')->keyBy('key')->all();
    }

    public function getFontSizes(): array
    {
        return $this->getSettingsByGroup('font_size')
            ->transform(function ($setting) {
                $setting->value = json_decode($setting->value, TRUE);

                return $setting;
            })
            ->keyBy('key')
            ->all();
    }

    public function getKeys()
    {
        $keys = $this->getSettingsByGroup('key.', true);
        $defaultKeys = collect(config('constants.settings.keys'));

        $defaultKeys->each(function ($defaultKey) use ($keys) {
            $isKeyNotExists = $keys->where('key', $defaultKey['key'])->isEmpty();

            if ($isKeyNotExists) {
                $keys->push($defaultKey);
            }
        });

        return $keys
            ->sortBy('group')
            ->groupBy('group')
            ->all();
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

    public function getLogoUrl(): ?string
    {
        return app(SettingCache::class)->remember('logo_url', function () {
            $media = $this->getLogoMedia();

            $dimensions = config('constants.dimensions.logo');

            return ($media
                ? $media->getOptimizedImageUrl(
                    $dimensions['width'],
                    $dimensions['height'],
                    'limitFit'
                )
                : null
            );
        });
    }

    public function getLogoOrDefaultUrl(): string
    {
        return $this->getLogoUrl()
            ?? StorageService::getImageUrl(config('constants.default_images.logo'));
    }

    public function getLogoWithDimensionOrDefault()
    {
        return app(SettingCache::class)->remember('logo_media', function () {
            $media = $this->getLogoMedia();

            $dimensions = config('constants.dimensions.logo');

            if ($media) {

                return [
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'url' => $media->getOptimizedImageUrl(
                        $dimensions['width'],
                        $dimensions['height'],
                        'limitFit'
                    ),
                ];
            }

            return [
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'url' => StorageService::getImageUrl(config('constants.default_images.logo')),
            ];

        });
    }

    public function getLogoMedia(): ?Media
    {
        $media = $this->getMediaFromSetting(
            config("constants.theme_header.header_logo_media.key")
        );

        if ($media) {
            $this->transformMedia($media);
        }

        return $media;
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

    public function getThemeFontSizes(): array
    {
        return array_merge(
            config('constants.theme_font_sizes'),
            Setting::where('group', 'font_size')
                ->get(['key', 'value'])
                ->mapWithKeys(function ($setting) {
                    $value = json_decode($setting->value, TRUE);

                    foreach (array_keys($value) as $device) {
                        $value[$device] = CssUnitConverter::pxToEm($value[$device] ?? 0);
                    }

                    return [
                        $setting->key => $value
                    ];
                })
                ->all()
        );
    }

    public function getThemeColors(): array
    {
        return array_merge(
            config('constants.theme_colors'),
            Setting::where('group', 'theme_color')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->all()
        );
    }

    public function getThemeFonts(): array
    {
        return Setting::where('group', 'font')
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->map(function($value) {
                return json_decode($value);
            })
            ->all();
    }

    public function getHomePage(): string
    {
        return $this->getKey('home_page');
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
        $media = $this->getMediaFromSetting('qrcode_public_page_logo_media_id');

        if ($media) {
            $this->transformMedia($media);
        }

        return $media;
    }

    public function getFaviconUrl(int $width = null): string
    {
        return app(SettingCache::class)->remember(
            'favicon_url'.($width ? '_'.$width : null),
            function () use ($width) {
                $media = $this->getFaviconMedia();

                if ($media) {
                    if (is_null($width)) {
                        return $media->file_url;
                    } else {
                        return $media->getThumbnailUrl($width, $width);
                    }
                }

                return "";
            }
        );
    }

    public function getFaviconMedia(): ?Media
    {
        $media = $this->getMediaFromSetting('favicon_media_id');

        if ($media) {
            $this->transformMedia($media);
        }

        return $media;
    }

    private function getMediaFromSetting(string $key): ?Media
    {
        $mediaId = $this->getKey($key);

        return $mediaId ? Media::find($mediaId) : null;
    }

    public function getSocialiteDrivers(): ?array
    {
        return app(SettingCache::class)
            ->remember('socialite_drivers', function () {
                $drivers = Setting::key('socialite_drivers')->value('value');
                $drivers = is_null($drivers) ? [] : json_decode($drivers);

                foreach ($drivers as $key => $driver) {
                    $oAuth = Setting::group('key.oauth_' . $driver)
                        ->get();

                    if (
                        $oAuth->isEmpty()
                        || $oAuth[0]->value === null
                        || $oAuth[1]->value === null
                    ) {
                        unset($drivers[$key]);
                    }
                }

                return $drivers;
            });
    }

    public function getGoogleApi(): string
    {
        return $this->getKey('google_api_key');
    }

    public function getRecaptchaKeys(): array
    {
        return app(SettingCache::class)->remember('google_recaptcha_keys', function () {
            return $this->getKeysByGroup('key.google_recaptcha');
        });
    }

    public function getIpRegistryApi(): string
    {
        return $this->getKey('ipregistry_api_key');
    }

    public function getOAuthFacebookKeys(): array
    {
        return app(SettingCache::class)->remember('oauth_facebook_keys', function () {
            return $this->getKeysByGroup('key.oauth_facebook');
        });
    }

    public function getOAuthGoogleKeys(): array
    {
        return app(SettingCache::class)->remember('oauth_google_keys', function () {
            return $this->getKeysByGroup('key.oauth_google');
        });
    }

    public function getOAuthTwitterKeys(): array
    {
        return app(SettingCache::class)->remember('oauth_twitter_keys', function () {
            return $this->getKeysByGroup('key.oauth_twitter');
        });
    }

    public function getStripeKeys(): array
    {
        return app(SettingCache::class)->remember('stripe_keys', function () {
            return $this->getKeysByGroup('key.stripe');
        });
    }

    public function getTinyMCEKey(): string
    {
        return $this->getKey('tinymce_api_key');
    }

    public function getDomainRedirections(): array
    {
        return app(SettingCache::class)->remember('domain_redirections', function () {
            $domainRedirections = Setting::key('domain_redirections')->value('value');

            return ($domainRedirections) ? json_decode($domainRedirections) : [];
        });
    }

    public function getKeysByGroup(string $group): array
    {
        return Setting::select([
                'key',
                'value'
            ])
            ->group($group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function isRecaptchaKeyExists(): bool
    {
        $recaptchaKeys = $this->getRecaptchaKeys();

        if (empty($recaptchaKeys)) {
            return false;
        }

        foreach ($recaptchaKeys as $key) {
            if (empty($key)) {
                return false;
            }
        }

        return true;
    }

    public function saveLogo(?int $mediaId): void
    {
        $setting = $this->saveKey('header_logo_media_id', $mediaId);

        $setting->syncMedia([
            $mediaId
        ]);
    }

    public function saveQrcodeLogo(?int $mediaId): void
    {
        $setting = $this->saveKey('qrcode_public_page_logo_media_id', $mediaId);

        $setting->syncMedia([
            $mediaId
        ]);
    }

    public function saveFavicon(?int $mediaId): void
    {
        $setting = $this->saveKey('favicon_media_id', $mediaId);

        $setting->syncMedia([
            $mediaId
        ]);
    }

    private function transformMedia(Media $media): void
    {
        $media->append(['is_image', 'thumbnail_url', 'display_file_name']);
    }

    public function adminDashboardWidgets(): Collection
    {
        $key = 'dashboard_widget_admin';

        return app(SettingCache::class)->remember($key, function () use ($key) {
            $value = Setting::key($key)->value('value');

            return ($value) ? collect(json_decode($value, true)) : collect();
        });
    }

    public function getRecaptchaScore(): float
    {
        $recaptchaScore = $this->getKey('recaptcha_score');

        if ($recaptchaScore == '') {
            return config('constants.settings.recaptcha.score');
        }

        return (float)$recaptchaScore;
    }

    public function getRegisterRecaptchaScore(): float
    {
        $recaptchaScore = $this->getKey('recaptcha_score_register');

        if ($recaptchaScore == '') {
            return config('constants.settings.recaptcha.score');
        }

        return (float)$recaptchaScore;
    }

    public function getForgotPasswordRecaptchaScore(): float
    {
        $recaptchaScore = $this->getKey('recaptcha_score_forgot_password');

        if ($recaptchaScore == '') {
            return config('constants.settings.recaptcha.score');
        }

        return (float)$recaptchaScore;
    }

    public static function maxFileSize(): int
    {
        $maxFileSize = self::getKey('max_file_size');

        if ($maxFileSize == "") {
            $maxFileSize = config('constants.max_file_size');
        }

        return (int)$maxFileSize;
    }

    public function isCookieConsentEnabled(): bool
    {
        $value = $this->getKey('cookie_consent_is_enabled');

        if ($value == "") {
            $value = false;
        }

        return boolval($value);
    }

    public function getCookieConsentMessage(): string
    {
        return Purifier::clean(
            $this->getKey('cookie_consent_message'),
            'tinymce'
        );
    }

    public function getCookieConsentMessageDecline(): string
    {
        return Purifier::clean(
            $this->getKey('cookie_consent_message_decline'),
            'tinymce'
        );
    }

    public function getCookieConsentRedirectDeclineUrl(): string
    {
        $currentLocale = currentLocale();
        $key = 'cookie_consent_redirect_decline_page_id';

        return app(SettingCache::class)
            ->remember($key . ':' . $currentLocale, function () use ($key, $currentLocale) {
                $value = Setting::key($key)->value('value');

                try {
                    $pageTranslation = Page::with([
                            'translations' => function ($query) {
                                $query->select([
                                    'id',
                                    'page_id',
                                    'locale',
                                    'slug',
                                ]);
                            },
                        ])
                        ->find($value)
                        ->translateOrDefault($currentLocale);
                } catch (\Throwable $th) {

                    return "";

                }

                return LaravelLocalization::localizeURL(
                    route('frontend.pages.show', [
                        'page_translation' => $pageTranslation->slug,
                    ]),
                    $currentLocale
                );
            });
    }

    public function defaultPasswordResetEmailSubject(): string
    {
        $value = Setting::key('user_password_reset_link_subject')->value('value');

        return $value ?? 'Reset Password Notification';
    }

    public function defaultPasswordResetEmailContent(): ?string
    {
        $value = Setting::key('user_password_reset_link_content')->value('value');

        return $value ?? '<h1>Hello {first_name} {last_name}!</h1>'.
            '<p>You are receiving this email because we received a password reset request for your account.</p>'.
            '<p>{password_reset_button_link}</p>'.
            '<p>This password reset link will expire on {expired_on}</p>'.
            '<p>If you did not request a password reset, no further action is required.</p><br><p>Regards,</p><p>{app_name}</p>';
    }
}
