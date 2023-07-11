<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use App\Services\SettingService;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ThemeSeoController extends CrudController
{
    protected $baseRouteName = 'admin.theme.seo';
    protected $title = 'Seo';

    private $settingService;
    private $themeService;

    public function __construct(
        SettingService $settingService,
        ThemeService $themeService
    ) {
        $this->settingService = $settingService;
        $this->themeService = $themeService;
    }

    public function edit()
    {
        $user = auth()->user();

        $postThumbnailMedia = $this->settingService->getPostThumbnailMedia();

        return Inertia::render(
            'ThemeSeo',
            $this->getData([
                'can' => [
                    'media' => [
                        'read' => $user->can('media.read'),
                        'add' => $user->can('media.add'),
                    ]
                ],
                'postThumbnailMedia' => $postThumbnailMedia,
                'instructions' => [
                    'postThumbnailMediaLibrary' => [
                        ...MediaService::defaultMediaLibraryInstructions(),
                        ...[
                            __('Recommended dimension: :dimension.', [
                                'dimension' => config('constants.recomended_dimensions.favicon')
                            ]),
                        ]
                    ],
                ],
                'i18n' => $this->translations(),
                'title' => Str::upper(__($this->title)),
            ])
        );
    }

    private function translations(): array
    {
        return [
            ...[
                'save' => __('Save'),
                'default_image' => __('Default image'),
                'post_thumbnail' => __('Post thumbnail'),
                'open_media_library' => __('Open media library'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
