<?php

namespace Modules\Space\Http\Controllers;

use App\Models\GlobalOption;
use App\Http\Controllers\CrudController;
use Inertia\Inertia;

class SettingController extends CrudController
{
    protected $title = "Space Setting";

    public function index()
    {
        return Inertia::render('Space::Settings/Index', $this->getData([
            'i18n' => [
                'spaceType' => __("Space Type"),
                'createSpaceType' => __("Create :resource", ['resource' => __("Space Type")]),
                'editSpaceType' => __("Edit :resource", ['resource' => __("Space Type")]),
            ],
            'can' => [
                'spaceType' => [
                    'view' => auth()->user()->can('viewAny', GlobalOption::class),
                ]
            ]
        ]));
    }
}
