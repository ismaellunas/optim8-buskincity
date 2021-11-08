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
            'menus' => fn () => (
                    auth()->check()
                    && (
                        $request->routeIs('admin.*')
                        || $request->routeIs('dashboard')
                        || $request->routeIs('user.profile.*')
                    )
                )
                ? MenuService::generateBackendMenu($request)
                : $this->menuService->generateMenus(TranslationSv::currentLanguage()),
            'menuSettings' => $this->settingService->getHeader(),
            'currentLanguage' => TranslationSv::currentLanguage(),
            'defaultLanguage' => TranslationSv::getDefaultLocale(),
            'languageOptions' => TranslationSv::getLocaleOptions(),
            'css.backend' => [
                'app' => mix('css/app.css')->toHtml(),
            ],
            'css.frontend' => [
                'app' => SettingService::getFrontendCssUrl(),
                'additional_css' => SettingService::getAdditionalCssUrl(),
                'tracking_code_after_body' => SettingService::getTrackingCodeAfterBodyUrl(),
                'tracking_code_before_body' => SettingService::getTrackingCodeBeforeBodyUrl(),
                'tracking_code_inside_head' => SettingService::getTrackingCodeInsideHeadUrl(),
            ],
            'js.frontend' => [
                'additional_javascript' => SettingService::getAdditionalJavascriptUrl(),
            ]
        ]);
    }
}
