<?php

namespace Modules\Space\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\Page;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\SpaceRequest;
use Modules\Space\Services\SpaceService;

class SpaceController extends CrudController
{
    protected $model = Space::class;
    protected $baseRouteName = 'admin.spaces';
    protected $pageService;
    protected $title = "Space";

    private $spaceService;

    public function __construct(SpaceService $spaceService)
    {
        $this->spaceService = $spaceService;
    }

    public function index(Request $request)
    {
        $parent = $request->parent;

        $parentSpace = Space::select(['id', 'name as value'])->find($parent);

        return Inertia::render('Space::SpaceIndex', $this->getData([
            'parentOptions' => $this->spaceService->parentOptions(),
            'spaces' => $this->spaceService->spaceTree($parent),
            'parent' => $parentSpace,
        ]));
    }

    public function create(Request $request)
    {
        $parentId = $request->parent;

        return Inertia::render('Space::SpaceCreate', $this->getData([
            'title' => 'Add New Space',
            'parentOptions' => $this->spaceService->parentOptions(
                $parentId ? [$parentId] : null
            ),
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
        ]));
    }

    public function store(SpaceRequest $request)
    {
        $space = new Space();

        $space->saveFromInputs($request->all());

        $request->session()->flash('message', 'Successfully creating '.$this->title.'!');

        return redirect()->route($this->baseRouteName.'.edit', $space->id);
    }

    public function edit(Space $space)
    {
        return Inertia::render('Space::SpaceEdit', $this->getData([
            'title' => 'Edit Space',
            'spaceRecord' => $space,
            'parentOptions' => $this
                ->spaceService
                ->parentOptions([$space->parent_id]),
        ]));
    }

    public function update(Request $request, Space $space)
    {
        $space->name = $request->name;
        $space->is_page_enabled = $request->is_page_enabled;
        $space->save();

        return back();
    }

    public function destroy(Request $request, Space $space)
    {
        $space->delete();
        $request->session()->flash('message', $this->title.' deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function moveNode(Request $request, $current, $parent = null)
    {
        $currentSpace = Space::find($current);

        if (! $parent) {
            $currentSpace->parent_id = null;
            $currentSpace->save();

            $currentSpace->updateDepth(0);

        } else {

            $parentSpace = Space::find($parent);
            $currentSpace->parent_id = $parentSpace->id;

            $currentSpace->updateDepth($parentSpace->depth + 1);
        }

        return back();
    }
}
