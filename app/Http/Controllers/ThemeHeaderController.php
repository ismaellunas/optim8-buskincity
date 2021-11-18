<?php

namespace App\Http\Controllers;

use App\Entities\CloudinaryStorage;
use App\Http\Requests\ThemeHeaderLayoutRequest;
use App\Models\{
    Media,
    Menu,
    MenuItem,
    Setting,
};
use App\Services\{
    MediaService,
    MenuService,
    SettingService,
    TranslationService,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ThemeHeaderController extends ThemeOptionController
{
    private $mediaService;
    private $menuService;
    private $settingService;
    private $modelMenu = Menu::class;
    private $modelMenuItem = MenuItem::class;

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
        $inputs = $request->all();

        $setting = Setting::firstOrNew(['key' => 'header_layout']);
        $setting->value = $inputs['layout'];
        $setting->save();

        if ($request->hasFile('logo.file')) {
            $media = $this->mediaService->uploadSetting(
                $inputs['logo']['file'],
                Str::random(10),
                new CloudinaryStorage(),
                (!App::environment('production') ? config('app.env') : null)
            );

            if ($inputs['logo']['media_id'] !== null) {
                $this->deleteMedia($inputs['logo']['media_id']);
            }

            $setting = Setting::firstOrNew([
                'key' => config("constants.theme_header.header_logo_media.key")
            ]);
            $setting->value = $media->id;
            $setting->save();
        }

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    private function deleteMedia($mediaId)
    {
        $media = Media::find($mediaId);

        if ($media) {
            $this->mediaService->destroy($media, new CloudinaryStorage());
        }
    }
}
