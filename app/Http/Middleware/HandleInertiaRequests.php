<?php

namespace App\Http\Middleware;

use App\Services\{
    MenuService,
    SettingService,
    TranslationService as TranslationSv,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    private $menuService;
    private $settingService;

    public function __construct(
        MenuService $menuService,
        SettingService $settingService
    ) {
        $this->menuService = $menuService;
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

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'debug' => config('app.debug'),
            'flash' => [
                'message' => fn () => $request->session()->get('message')
            ],
            'success' => [
                'message' => fn () => $request->session()->get('success')
            ],
            'failed' => [
                'message' => fn () => $request->session()->get('failed')
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
            'logoUrl' => $this->settingService->getLogoUrl(),
            'menus' => function () use ($request) {
                if (
                    auth()->check()
                    && (
                        $request->routeIs('admin.*')
                        || $request->routeIs('dashboard')
                        || $request->routeIs('user.profile.*')
                    )
                ) {
                    return MenuService::generateBackendMenu($request);
                } elseif (
                    !auth()->check()
                    && $request->routeIs('admin.*')
                ) {
                    return [];
                }
                return $this->menuService->getHeaderMenu(TranslationSv::currentLanguage());
            },
            'headerLayout' => $this->settingService->getHeaderLayout(),
            'currentLanguage' => TranslationSv::currentLanguage(),
            'defaultLanguage' => TranslationSv::getDefaultLocale(),
            'languageOptions' => TranslationSv::getLocaleOptions(),
            'css.frontend' => [
                'app' => SettingService::getFrontendCssUrl(),
            ]
        ]);
    }
}
