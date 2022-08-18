<?php

namespace Modules\Space\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class ProductManagerController extends Controller
{
    use FlashNotifiable;

    private $spaceService;

    public function __construct(SpaceService $spaceService)
    {
        $this->spaceService = $spaceService;
    }

    public function search(Request $request)
    {
        return $this->spaceService->managers(
            $request->term,
            $request->excluded ?? []
        );
    }

    public function updateManagers(Request $request, Space $space)
    {
        $space->productManagers()->sync($request->managers);

        $this->generateFlashMessage('Product Manager updated successfully!');

        return back();
    }
}
