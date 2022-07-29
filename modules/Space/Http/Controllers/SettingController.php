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
            'can' => [
                'spaceType' => [
                    'view' => auth()->user()->can('viewAny', GlobalOption::class),
                ]
            ]
        ]));
    }
}
