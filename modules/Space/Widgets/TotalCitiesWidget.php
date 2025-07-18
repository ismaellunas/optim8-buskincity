<?php

namespace Modules\Space\Widgets;

use App\Contracts\WidgetInterface;
use Illuminate\Support\Arr;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\ModuleService as FormBuilderModuleService;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService as SpaceModuleService;
use Modules\Space\Services\SpaceService;

class TotalCitiesWidget implements WidgetInterface
{
    private $vueComponent = 'TotalWidget';
    private $vueComponentModule = null;
    private array $storedSetting;
    private $formId = null;
    private $cityType;

    public function __construct(array $storedSetting)
    {
        $this->storedSetting = $storedSetting;
        $this->formId = Arr::get($storedSetting, 'setting.form_id');

        $this->cityType = app(SpaceService::class)->typeOptions()->firstWhere('value', 'City')['id'];
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
            app(SpaceModuleService::class)->isModuleActive()
            && auth()->user()->can('totalSpaceByTypeWidget', [Space::class, $this->cityType])
        );
    }

    public function response()
    {
        $totals = collect([
                [ ...$this->moduleResponseForm() ],
                [
                    'text' => app(SpaceService::class)->totalSpaceByType(
                        auth()->user(),
                        $this->cityType
                    ),
                    'url' => $this->viewUrl(['types' => [$this->cityType]]),
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
            ! app(FormBuilderModuleService::class)->isModuleActive()
            || ! auth()->user()->can('viewAny', Form::class)
            || ! $this->formId
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
