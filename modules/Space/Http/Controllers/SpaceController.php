<?php

namespace Modules\Space\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Services\PageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\SpaceRequest;
use Modules\Space\Services\SpaceService;

class SpaceController extends CrudController
{
    protected $model = Space::class;
    protected $baseRouteName = 'admin.spaces';
    protected $title = "Space";

    private $spaceService;
    private $pageService;

    public function __construct(SpaceService $spaceService, PageService $pageService)
    {
        $this->authorizeResource(Space::class, 'space');

        $this->spaceService = $spaceService;
        $this->pageService = $pageService;
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
            'typeOptions' => $this->spaceService->typeOptions(),
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
        ]));
    }

    public function store(SpaceRequest $request)
    {
        $space = new Space();

        $inputs = $request->all();

        $space->saveFromInputs($inputs);

        $request->session()->flash('message', 'Successfully creating '.$this->title.'!');

        return redirect()->route($this->baseRouteName.'.edit', $space->id);
    }

    private function makePage(): Page
    {
        $page = new Page();

        $pageTranslation = (new PageTranslation())->fill([
            'title' => null,
            'slug' => null,
            'unique_key' => null,
        ]);

        $page->setRelation(
            'translations',
            collect()->push($pageTranslation)
        );

        return $page;
    }

    public function edit(Space $space)
    {
        $user = auth()->user();

        $spaceManagers = $space->managers->map(function ($manager) {
            return [
                'id' => $manager->id,
                'value' => $manager->fullName,
            ];
        });

        $parent = (
            $space->parent_id
            ? [
                'id' => $space->parent_id,
                'value' => $space->parent->name,
            ]
            : null
        );

        $page = $space->page;

        $images = [];

        if (! $page) {
            $page = $this->makePage();

        } else {

            $page->load('translations');

            $images = $this->pageService->getImagesFromPage($page);
        }

        return Inertia::render('Space::SpaceEdit', $this->getData([
            'title' => 'Edit Space',
            'spaceRecord' => $space,
            'parentOptions' => $parent ? [$parent] : [],
            'typeOptions' => $this->spaceService->typeOptions(),
            'spaceManagers' => $spaceManagers,
            'can' => [
                'media' => [
                    'browse' => $user->can('media.browse'),
                    'read' => $user->can('media.read'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ],
                'page' => [
                    'read' => $user->can('space.read'),
                ],
            ],
            'statusOptions' => Page::getStatusOptions(),
            'page' => $page,
            'images' => $images,
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
        ]));
    }

    public function update(SpaceRequest $request, Space $space)
    {
        $inputs = $request->all();
        $space->saveFromInputs($inputs);

        $request->session()->flash('message', 'Successfully updating '.$this->title.'!');

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
