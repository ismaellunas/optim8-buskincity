<?php

namespace Modules\FormBuilder\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;

class TotalEntriesWidget implements WidgetInterface
{
    private $vueComponent = 'TotalWidget';
    private $vueComponentModule = null;
    private $storedSetting;
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

    private function viewUserUrl($queryParams = [])
    {
        return route('admin.users.index', $queryParams);
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
        return auth()->user()->can('viewAny', Form::class);
    }

    public function response()
    {
        $unreadFormTotal = FormEntry::where('form_id', $this->formId)
            ->read(false)
            ->count();

        $performerRole = Role::findByName(
                config('permission.role_names.performer'),
                'web'
            );

        $performerTotal = User::role(
                $performerRole->id ?? null
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
                    'url' => $this->viewUserUrl(
                        ['roles' => [ $performerRole->id ?? null ]]
                    ),
                ]
            ],
        ]);
    }
}
