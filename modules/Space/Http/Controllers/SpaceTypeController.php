<?php

namespace Modules\Space\Http\Controllers;

use App\Models\GlobalOption;
use App\Services\GlobalOptionService;
use Illuminate\Http\Request;
use Modules\Space\Http\Requests\SpaceTypeRequest;
use App\Http\Controllers\CrudController;

class SpaceTypeController extends CrudController
{
    public function __construct()
    {
        $this->authorizeResource(GlobalOption::class, 'space_type');
    }

    public function store(SpaceTypeRequest $request)
    {
        $inputs = $request->validated();
        $inputs['key'] = config('space.type_option');

        $globalOption = new GlobalOption();

        $globalOption->saveFromInputs($inputs);

        $this->generateFlashMessage('Space type created successfully!');

        return redirect()->back();
    }

    public function update(Request $request, GlobalOption $spaceType)
    {
        $spaceType->saveFromInputs($request->all());

        $this->generateFlashMessage('Space type updated successfully!');

        return redirect()->back();
    }

    public function destroy(GlobalOption $spaceType)
    {
        $spaceType->delete();

        $this->generateFlashMessage('Space type deleted successfully!');

        return redirect()->back();
    }

    public function records(Request $request)
    {
        return app(GlobalOptionService::class)->getRecords(
            $request->term,
            [
                'key' => config('space.type_option')
            ]
        );
    }

    public function apiValidateSpaceType(SpaceTypeRequest $request)
    {
        return response('passed', 200);
    }
}
