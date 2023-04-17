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
                'i18n' => $this->translations(),
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

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Header Layout')
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    private function translations()
    {
        return [
            ...[
                'layout' => __('Layout'),
                'navigation' => __('Navigation'),
                'header_layout' => __('Header layout'),
                'standard' => __('Standard'),
                'centered_logo' => __('Centered logo'),
                'standard_with_social_media' => __('Standard with social media'),
                'logo' => __('Logo'),
                'open_media_library' => __('Open media library'),
                'save' => __('Save'),
                'menu_items' => __('Menu items'),
                'add_menu_item' => __('Add :resource', ['resource' => __('Menu item')]),
                'edit_menu_item' => __('Edit :resource', ['resource' => __('Menu item')]),
                'duplicate_menu' => __('Duplicate menu'),
                'to' => __('To'),
                'title' => __('Title'),
                'type' => __('Type'),
                'url' => __('Url'),
                'menu' => __('Menu'),
                'open_link' => __('Open link in a new tab'),
                'cancel' => __('Cancel'),
                'create' => __('Create'),
                'update' => __('Update'),
                'duplicate' => __('Duplicate'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
