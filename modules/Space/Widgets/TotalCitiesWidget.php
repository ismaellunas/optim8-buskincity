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

    private function viewUrl($queryParams = []): string
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

        $totals = collect([
                [ ...$this->moduleResponseForm() ],
                [
                    'text' => app(SpaceService::class)->totalSpaceByType(
                        auth()->user(),
                        $spaceTypeId
                    ),
                    'url' => $this->viewUrl(['types' => [$spaceTypeId]]),
                ]
            ])
            ->filter()
            ->values();

        return response()->json([
            'totals' => $totals,
        ]);
    }

    private function moduleResponseForm(): array
    {
        if (
            ! app(ModuleService::class)->isModuleActive('formbuilder')
            || ! auth()->user()->can('viewAny', Form::class)
        ) {
            return [];
        }

        $unreadFormTotal = FormEntry::where('form_id', $this->formId)
            ->read(false)
            ->count();

        return [
            'text' => $unreadFormTotal,
            'url' => route('admin.form-builders.entries.index', [
                'form_builder' => $this->formId,
                'read' => 0,
            ]),
        ];
    }
}
