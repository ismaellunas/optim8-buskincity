<?php

namespace Modules\Space\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\GlobalOption;
use App\Services\ModuleService;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class TotalCitiesWidget implements WidgetInterface
{
    private $vueComponent = 'TotalWidget';
    private $vueComponentModule = null;
    private array $storedSetting;

    public function __construct(array $storedSetting)
    {
        $this->storedSetting = $storedSetting;
    }

    private function url(): string
    {
        return route('admin.api.widget.data', [
            'uuid' => $this->storedSetting['uuid']
        ]);
    }

    private function viewUrl($queryParams = [])
    {
        return route('admin.spaces.index', $queryParams);
    }

    public function data(): array
    {
        return [
            'title' => $this->storedSetting['title'] ?? 'Cities',
            'url' => $this->url(),
            'module' => null,
            'vueComponent' => $this->vueComponent,
            'vueComponentModule' => $this->vueComponentModule,
            'grid' => $storedSetting['grid'] ?? 6,
            'backgroudColor' => $this->storedSetting['background_color'],
        ];
    }

    public function canBeAccessed(): bool
    {
        return (
            app(ModuleService::class)->isModuleActive('space')
            && auth()->user()->can('totalSpaceByTypeWidget', [Space::class, 19])
        );
    }

    public function response()
    {
        $spaceTypeId = GlobalOption::type(config('space.type_option'))
            ->name('City')
            ->select('id')
            ->first()->id;

        return response()->json([
            'totals' => [
                [
                    'text' => app(SpaceService::class)->totalSpaceByType(
                        auth()->user(),
                        $spaceTypeId
                    ),
                    'url' => $this->viewUrl(['types' => [$spaceTypeId]]),
                ]
            ]
        ]);
    }
}
