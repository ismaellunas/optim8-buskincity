<?php

namespace App\Http\Controllers;

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
use Inertia\Inertia;

class ThemeHeaderController extends CrudController
{
    private $menuService;
    private $settingService;
    private $modelMenu = Menu::class;

    protected $baseRouteName = 'admin.theme.header';
    protected $componentName = 'ThemeHeader/';
    protected $title = "Header";

    public function __construct(
        MenuService $menuService,
        SettingService $settingService
    ) {
        $this->menuService = $menuService;
        $this->settingService = $settingService;
    }

    public function edit()
    {
        $user = auth()->user();

        $logoMedia = $this->settingService->getLogoMedia();

        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'can' => [
                    'media' => [
                        'read' => $user->can('media.read'),
                        'add' => $user->can('media.add'),
                    ]
                ],
                'headerMenus' => $this->menuService->getHeaderMenus(
                    app(TranslationService::class)->getLocales()
                ),
                'logoMedia' => $logoMedia,
                'menu' => $this->modelMenu::header()->first(),
                'menuOptions' => $this->menuService->getMenuOptions(),
                'settings' => $this->settingService->getHeader(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
                'instructions' => [
                    'mediaLibrary' => MediaService::defaultMediaLibraryInstructions(),
                ],
            ]),
        );
    }

    public function update(ThemeHeaderLayoutRequest $request)
    {
        $inputs = $request->validated();

        $setting = Setting::firstOrNew(['key' => 'header_layout']);
        $setting->value = $inputs['layout'];
        $setting->save();

        if ($request->has('logo')) {
            $this->settingService->saveLogo($inputs['logo']);
        }

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
