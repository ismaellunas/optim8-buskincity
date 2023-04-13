<?php

namespace Modules\Space\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\GlobalOption;
use App\Services\ModuleService;
use Illuminate\Support\Arr;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class TotalCitiesWidget implements WidgetInterface
{
    private $vueComponent = 'TotalWidget';
    private $vueComponentModule = null;
    private array $storedSetting;
    private $formId;

    public function __construct(array $storedSetting)
    {
        $this->storedSetting = $storedSetting;
        $this->formId = Arr::get($storedSetting, 'setting.form_id');
    }

    private function url(): string
    {
        return route('admin.api.widget.data', [
            'uuid' => $this->storedSetting['uuid']
        ]);
    }

    private function viewFormUrl($queryParams = [])
    {
        return route('admin.form-builders.entries.index', array_merge(
            [ 'form_builder' => $this->formId ],
            $queryParams,
        ));
    }

    private function viewCityUrl($queryParams = [])
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
            && auth()->user()->can('viewAny', Form::class)
        );
    }

    public function response()
    {
        $unreadFormTotal = FormEntry::where('form_id', $this->formId)
            ->read(false)
            ->count();

        $spaceTypeId = GlobalOption::type(config('space.type_option'))
            ->name('City')
            ->select('id')
            ->first()->id;

        return response()->json([
            'totals' => [
                [
                    'text' => $unreadFormTotal,
                    'url' => $this->viewFormUrl(['read' => 0]),
                ],
                [
                    'text' => app(SpaceService::class)->totalSpaceByType(
                        auth()->user(),
                        $spaceTypeId
                    ),
                    'url' => $this->viewCityUrl(['types' => [$spaceTypeId]]),
                ]
            ]
        ]);
    }
}
