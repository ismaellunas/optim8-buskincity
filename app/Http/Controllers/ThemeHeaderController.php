<?php

namespace App\Http\Controllers;

use App\Actions\UploadLogo;
use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Http\Requests\ThemeHeaderLayoutRequest;
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

class ThemeHeaderController extends CrudController
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
                'headerMenus' => $this->menuService->getHeaderMenus(
                    app(TranslationService::class)->getLocales()
                ),
                'logoUrl' => $this->settingService->getLogoUrl(),
                'menu' => $this->modelMenu::header()->first(),
                'menuOptions' => $this->menuService->getMenuOptions(),
                'settings' => $this->settingService->getHeader(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
                'instructions' => [
                    'logo' => [
                        __('Accepted file extensions: :extensions.', [
                            'extensions' => implode(', ', config('constants.extensions.image'))
                        ]),
                        __('Max file size: :filesize.', [
                            'filesize' => HumanReadable::bytesToHuman(
                                (50 * config('constants.one_megabyte')) * 1024
                            )
                        ]),
                    ]
                ]
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
            $uploadLogo = new UploadLogo();

            $media = $uploadLogo->handle($inputs['logo']);

            $this->settingService->saveLogo($media->id);
        }

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
