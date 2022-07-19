<?php

namespace Modules\Space\Http\Controllers;

use App\Http\Controllers\CrudController;
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
        $this->authorizeResource(Space::class, 'space');

        $this->spaceService = $spaceService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $spaces = $this->spaceService->spaceTree($user, $request->parent);

        $spaceOptions = $this->spaceService->parentOptions($user);

        return Inertia::render('Space::SpaceIndex', $this->getData([
            'isSortableEnabled' => $user->can('changeParent', Space::class),
            'parent' => $request->parent,
            'parentOptions' => $spaceOptions,
            'spaces' => $spaces,
        ]));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if ($request->parent) {
            $parentOptions = [Space::select('id', 'name as value')->find($request->parent)];
        } else {
            $parentOptions = $this->spaceService->parentOptions(
                $user,
                $user->can('space.create')
            );
        }

        return Inertia::render('Space::SpaceCreate', $this->getData([
            'title' => 'Add New Space',
            'parentOptions' => $parentOptions,
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
        $spaceManagers = $space->managers->map(function ($manager) {
            return [
                'id' => $manager->id,
                'value' => $manager->fullName,
            ];
        });

        $parent = null;

        if ($space->parent_id) {
            $parent = [
                'id' => $space->parent_id,
                'value' => $space->parent->name,
            ];
        }

        return Inertia::render('Space::SpaceEdit', $this->getData([
            'title' => 'Edit Space',
            'spaceRecord' => $space,
            'parentOptions' => $parent ? [$parent] : [],
            'spaceManagers' => $spaceManagers,
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

    public function moveNode($current, $parent = null)
    {
        $this->authorize('changeParent', Space::class);

        $currentSpace = Space::find($current);

        if (! $parent) {
            $currentSpace->saveAsRoot();

        } else {

            $parentSpace = Space::find($parent);
            $parentSpace->appendNode($currentSpace);
        }

        return back();
    }

    public function searchManagers(Request $request)
    {
        return $this->spaceService->managers(
            $request->term,
            $request->excluded ?? []
        );
    }

    public function updateManagers(Request $request, Space $space)
    {
        $space->managers()->sync($request->managers);

        $request->session()->flash('message', 'Space created successfully!');

        return back();
    }
}
