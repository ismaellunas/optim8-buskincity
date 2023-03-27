<?php

namespace Modules\FormBuilder\Widgets;

use App\Contracts\WidgetInterface;
use Modules\FormBuilder\Entities\Form;

class EntryWidget implements WidgetInterface
{
    private $user;
    private $componentName = "FormEntry";
    private $title = "Form Entries";

    private $baseRouteName = "admin.form-builders";

    public function __construct($request)
    {
        $this->user = $request->user();
    }

    public function data(): array
    {
        return [
            'title' => __($this->title),
            'componentName' => $this->componentName,
            'moduleName' => config('formbuilder.name'),
            'data' => [
                'formLists' => $this->getFormLists(),
                'baseRouteName' => $this->baseRouteName,
            ],
            'order' => 3,
            'i18n' => [
                'view_detail' => __('View Detail'),
                'view_all' => __('View All'),
                'no_data' => __('No Data'),
            ]
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('form_builder.browse');
    }

    private function getFormLists(): array
    {
        return Form::select([
                'id',
                'name',
            ])
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }
}
