<?php

namespace Modules\Space\Http\Controllers;

use App\Models\GlobalOption;
use App\Services\GlobalOptionService;
use Illuminate\Http\Request;
use Modules\Space\Http\Requests\SpaceTypeRequest;
use App\Http\Controllers\CrudController;

class SpaceTypeController extends CrudController
{
    protected $title = "Space type";

    public function __construct()
    {
        $this->authorizeResource(GlobalOption::class, 'space_type');
    }

    public function store(SpaceTypeRequest $request)
    {
        $inputs = $request->validated();
        $inputs['type'] = config('space.type_option');

        $globalOption = new GlobalOption();

        $globalOption->saveFromInputs($inputs);

        $this->generateFlashMessage("The :resource was created!", ['resource' => $this->title()]);

        return redirect()->back();
    }

    public function update(Request $request, GlobalOption $spaceType)
    {
        $spaceType->saveFromInputs($request->all());

        $this->generateFlashMessage("The :resource was updated!", ['resource' => $this->title()]);

        return redirect()->back();
    }

    public function destroy(GlobalOption $spaceType)
    {
        $spaceType->delete();

        $this->generateFlashMessage("The :resource was deleted!", ['resource' => $this->title()]);

        return redirect()->back();
    }

    public function records(Request $request)
    {
        return app(GlobalOptionService::class)->getRecords(
            $request->term,
            [
                'type' => config('space.type_option')
            ]
        );
    }

    public function apiValidateSpaceType(SpaceTypeRequest $request)
    {
        return response('passed', 200);
    }
}
