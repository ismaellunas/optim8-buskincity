<?php

namespace App\Http\Controllers;

use App\Entities\CloudinaryStorage;
use App\Http\Requests\ThemeHeaderLayoutRequest;
use App\Jobs\UpdateStripeConnectedAccountBrandingLogo;
use App\Models\{
    Menu,
    Setting,
};
use App\Services\{
    MediaService,
    MenuService,
    SettingService,
    TranslationService,
};
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ThemeHeaderController extends ThemeOptionController
{
    private $mediaService;
    private $menuService;
    private $settingService;
    private $modelMenu = Menu::class;

    protected $baseRouteName = 'admin.theme.header';
    protected $componentName = 'ThemeHeader/';
    protected $title = "Header";

    public function __construct(
        MediaService $mediaService,
        MenuService $menuService,
        SettingService $settingService
    ) {
        $this->mediaService = $mediaService;
        $this->menuService = $menuService;
        $this->settingService = $settingService;
    }

    public function edit()
    {
        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'categoryOptions' => $this->menuService->getCategoryOptions(),
                'menu' => $this->modelMenu::header()->first(),
                'headerMenus' => $this->menuService->getHeaderMenus(
                    TranslationService::getLocales()
                ),
                'logoUrl' => $this->settingService->getLogoUrl(),
                'pageOptions' => $this->menuService->getPageOptions(),
                'postOptions' => $this->menuService->getPostOptions(),
                'settings' => $this->settingService->getHeader(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
            ]),
        );
    }

    public function update(ThemeHeaderLayoutRequest $request)
    {
        $inputs = $request->validated();

        $setting = Setting::firstOrNew(['key' => 'header_layout']);
        $setting->value = $inputs['layout'];
        $setting->save();

        if ($request->hasFile('logo')) {
            $media = $this->mediaService->uploadSetting(
                $inputs['logo'],
                Str::random(10),
                new CloudinaryStorage(),
                (!App::environment('production') ? config('app.env') : null)
            );

            $existingMedia = $this->settingService->getLogoMedia();

            $setting = Setting::firstOrNew([
                'key' => config("constants.theme_header.header_logo_media.key")
            ]);
            $setting->value = $media->id;
            $setting->save();

            if ($existingMedia) {
                $this->mediaService->destroy(
                    $existingMedia,
                    new CloudinaryStorage()
                );
            }

            $job = new UpdateStripeConnectedAccountbrandingLogo($media);
            $job->delay(now()->addMinutes(1));

            dispatch($job);
        }

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
