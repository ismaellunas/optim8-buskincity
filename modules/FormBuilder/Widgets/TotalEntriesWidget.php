<?php

namespace Modules\FormBuilder\Widgets;

use App\Contracts\WidgetInterface;
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

    private function viewUrl($queryParams = [])
    {
        return route('admin.form-builders.entries.index', array_merge(
            [ 'form_builder' => $this->formId ],
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
        return auth()->user()->can('viewAny', Form::class);
    }

    public function response()
    {
        $subTotal = FormEntry::where('form_id', $this->formId)
            ->read(false)
            ->count();

        $total = FormEntry::where('form_id', $this->formId)->count();

        return response()->json([
            'totals' => [
                [
                    'text' => $subTotal,
                    'url' => $this->viewUrl(['read' => 0]),
                ],
                [
                    'text' => $total,
                    'url' => $this->viewUrl(),
                ]
            ],
        ]);
    }
}
