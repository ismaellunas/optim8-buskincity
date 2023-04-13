<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\Role;
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

    private function viewFormUrl($queryParams = [])
    {
        return route('admin.form-builders.entries.index', array_merge(
            [ 'form_builder' => $this->formId ],
            $queryParams,
        ));
    }

    private function viewUserUrl($queryParams = [])
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
        return (
            app(ModuleService::class)->isModuleActive('formbuilder')
            && auth()->user()->can('viewAny', User::class)
            && auth()->user()->can('viewAny', Form::class)
        );
    }

    public function response()
    {
        $unreadFormTotal = FormEntry::where('form_id', $this->formId)
            ->read(false)
            ->count();

        $performerTotal = User::role(
                $this->roleId ?? null
            )
            ->available()
            ->count();

        return response()->json([
            'totals' => [
                [
                    'text' => $unreadFormTotal,
                    'url' => $this->viewFormUrl(['read' => 0]),
                ],
                [
                    'text' => $performerTotal,
                    'url' => $this->viewUserUrl(),
                ]
            ],
        ]);
    }
}
