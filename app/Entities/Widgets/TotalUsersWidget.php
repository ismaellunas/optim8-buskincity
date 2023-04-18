<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Support\Arr;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;

class TotalUsersWidget implements WidgetInterface
{
    private $vueComponent = 'TotalWidget';
    private $vueComponentModule = null;
    private $storedSetting;
    private $formId;
    private $roleId;

    public function __construct(array $storedSetting)
    {
        $this->storedSetting = $storedSetting;
        $this->formId = Arr::get($storedSetting, 'setting.form_id');
        $this->roleId = Arr::get($storedSetting, 'setting.role_id');
    }

    private function url(): string
    {
        return route('admin.api.widget.data', [
            'uuid' => $this->storedSetting['uuid']
        ]);
    }

    private function viewUrl($queryParams = [])
    {
        return route('admin.users.index', array_merge(
            ['roles' => [ $this->roleId ]] ,
            $queryParams,
        ));
    }

    public function data(): array
    {
        return [
            'title' => $this->storedSetting['title'] ?? 'Entries',
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
        return auth()->user()->can('viewAny', User::class);
    }

    public function response()
    {
        $total = User::role(
                $this->roleId ?? null
            )
            ->available()
            ->count();

        $totals = collect([
                [ ...$this->moduleResponseForm() ],
                [
                    'text' => $total,
                    'url' => $this->viewUrl(),
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
