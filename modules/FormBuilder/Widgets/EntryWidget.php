<?php

namespace Modules\FormBuilder\Widgets;

use App\Contracts\WidgetInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\FieldGroup;

class EntryWidget implements WidgetInterface
{
    private $user;
    private $componentName = "Entry";
    private $title = "Form Entries";

    protected $baseRouteName = "admin.form-builders";

    public function __construct($request)
    {
        $this->user = $request->user();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'moduleName' => config('formbuilder.name'),
            'data' => [
                'formLists' => $this->getFormLists(),
                'baseRouteName' => $this->baseRouteName,
            ],
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('form_builder.browse');
    }

    private function getFormLists(): array
    {
        return FieldGroup::select([
                'id',
                'name',
            ])
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }
}
