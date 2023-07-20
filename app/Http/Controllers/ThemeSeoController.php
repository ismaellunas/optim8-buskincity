<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeSeoRequest;
use App\Services\MediaService;
use App\Services\SettingService;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ThemeSeoController extends CrudController
{
    protected $baseRouteName = 'admin.theme.seo';
    protected $title = 'Seo';

    private $settingService;

    public function __construct(
        SettingService $settingService,
    ) {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        $user = auth()->user();

        return Inertia::render(
            'ThemeSeo',
            $this->getData([
                'can' => [
                    'media' => [
                        'read' => $user->can('media.read'),
                        'add' => $user->can('media.add'),
                    ]
                ],
                'postThumbnailMedia' => $this->settingService->getPostThumbnailMedia(),
                'openGraphMedia' => $this->settingService->getOpenGraphMedia(),
                'instructions' => [
                    'postThumbnailMediaLibrary' => [
                        ...MediaService::defaultMediaLibraryInstructions(),
                        ...[
                            __('Recommended dimension: :dimension.', [
                                'dimension' => config('constants.recomended_dimensions.post_thumbnail')
                            ]),
                        ]
                    ],
                    'openGraphMediaLibrary' => [
                        ...MediaService::defaultMediaLibraryInstructions(),
                        ...[
                            __('Recommended dimension: :dimension.', [
                                'dimension' => config('constants.recomended_dimensions.open_graph')
                            ]),
                        ]
                    ],
                ],
                'i18n' => $this->translations(),
                'title' => $this->title(),
            ])
        );
    }

    public function update(ThemeSeoRequest $request)
    {
        $inputs = $request->validated();

        foreach ($inputs as $key => $code) {

            switch ($key) {
                case 'post_thumbnail':
                    $this->settingService->savePostThumbnail($inputs[$key]);
                    break;

                case 'open_graph':
                    $this->settingService->saveOpenGraph($inputs[$key]);
                    break;

                default:
                    $this->settingService->saveKey($key, $code);
                    break;
            }
        }

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title(),
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    protected function title(): string
    {
        return Str::upper(__($this->title));
    }

    private function translations(): array
    {
        return [
            ...[
                'save' => __('Save'),
                'default_image' => __('Default image'),
                'post_thumbnail' => __('Post thumbnail'),
                'open_graph' => __('Open graph'),
                'open_media_library' => __('Open media library'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
