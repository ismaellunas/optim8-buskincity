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

        $logoMedia = $this->settingService->getLogoForMediaLibrary();

        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'can' => [
                    'media' => [
                        'add' => $user->can('media.add'),
                        'browse' => $user->can('media.browse'),
                        'edit' => $user->can('media.edit'),
                        'read' => $user->can('media.read'),
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
                    'mediaLibrary' => MediaService::logoMediaLibraryInstructions(),
                ],
                'i18n' => $this->translations(),
                'dimensions' => [
                    'logo' => config('constants.dimensions.logo'),
                ],
            ]),
        );
    }

    public function update(ThemeHeaderLayoutRequest $request)
    {
        $inputs = $request->validated();

        $setting = Setting::firstOrNew(
            ['key' => 'header_layout'],
            ['group' => 'header'],
        );
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
                'add_menu_item' => __('Add :resource', ['resource' => __('Menu item')]),
                'cancel' => __('Cancel'),
                'centered_logo' => __('Centered logo'),
                'create' => __('Create'),
                'duplicate_menu' => __('Duplicate menu'),
                'duplicate' => __('Duplicate'),
                'edit_menu_item' => __('Edit :resource', ['resource' => __('Menu item')]),
                'header_layout' => __('Header layout'),
                'layout' => __('Layout'),
                'logo' => __('Logo'),
                'menu_items_delete' => __('A nested menu will get deleted.'),
                'menu_items' => __('Menu items'),
                'menu' => __('Menu'),
                'navigation' => __('Navigation'),
                'nested_menu_error_message' => __('Cannot add more than 2 levels of nested menus.'),
                'open_link' => __('Open link in a new tab'),
                'open_media_library' => __('Open media library'),
                'save' => __('Save'),
                'standard_with_social_media' => __('Standard with social media'),
                'standard' => __('Standard'),
                'title' => __('Title'),
                'to' => __('To'),
                'type' => __('Type'),
                'update' => __('Update'),
                'url' => __('Url'),
                'drag_and_drop' => __('Drag and drop'),
                'tips' => [
                    'menu_items' => __('This menu will be shown on the navbar, drag and drop to reorganize the menu items.'),
                ],
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
