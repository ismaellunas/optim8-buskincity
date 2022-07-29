<?php

namespace App\Http\Middleware;

use App\Services\{
    MenuService,
    SettingService,
    TranslationService as TranslationSv,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    private $settingService;

    public function __construct(
        SettingService $settingService
    ) {
        $this->settingService = $settingService;
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    public function rootView(Request $request)
    {
        if ($request->routeIs('admin.*')) {
            return 'admin';
        }

        return $this->rootView;
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        $sharedUserData = Inertia::getShared('user')() ?? [];

        return array_merge(parent::share($request), [
            'csrfToken' => csrf_token(),
            'debug' => config('app.debug'),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'message_expired' => fn () => $request->session()->get('message_expired')
            ],
            'errors' => function () {
                if (Session::get('errors')) {
                    $bags = [];
                    foreach (Session::get('errors')->getBags() as $bag => $error) {
                        $bags[$bag] = $error->getMessages();
                    }
                    return $bags;
                }
                return (object)[];
            },
            'appLogoUrl' => $this->settingService->getLogoUrl(),
            'menus' => $this->getHeaderMenus($request),
            'footerMenus' => $this->getFooterMenus($request),
            'currentLanguage' => TranslationSv::currentLanguage(),
            'defaultLanguage' => TranslationSv::getDefaultLocale(),
            'languageOptions' => app(TranslationSv::class)->getLocaleOptions(),
            'css.frontend' => [
                'app' => SettingService::getFrontendCssUrl(),
            ],
            'user' => function () use ($sharedUserData) {
                return $this->removeSensitiveDataExposure($sharedUserData);
            },
            'userOriginLanguage' => $request->user()->origin_language_code ?? null,
        ]);
    }

    private function removeSensitiveDataExposure(array $sharedUserData): array
    {
        $sensitiveKeys = [
            'connected_accounts',
            'created_at',
            'current_connected_account_id',
            'email_verified_at',
            'is_suspended',
            'language_id',
            'origin_language',
            'permissions',
            'profile_photo',
            'profile_photo_media_id',
            'roles',
            'updated_at',
        ];

        foreach ($sensitiveKeys as $key) {
            unset($sharedUserData[$key]);
        }

        return $sharedUserData;
    }

    private function getHeaderMenus($request): array
    {
        $user = $request->user();

        if ($user) {
            if (
                $request->routeIs('admin.*')
                && $user->can('system.dashboard')
            ) {
                return app(MenuService::class)->getBackendNavMenus($request);
            } else {
                return app(MenuService::class)->getFrontendUserMenus($request);
            }
        }

        return [];
    }

    private function getFooterMenus($request): array
    {
        $user = $request->user();

        if ($user) {
            if (
                $request->routeIs('admin.*')
                && $user->can('system.dashboard')
            ) {
                return [];
            } else {
                return app(MenuService::class)->getFrontendUserFooterMenus($request);
            }
        }

        return [];
    }
}
