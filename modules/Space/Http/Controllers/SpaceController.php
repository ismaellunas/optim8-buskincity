<?php

namespace Modules\Space\Http\Controllers;

use App\Helpers\HumanReadable;
use App\Http\Controllers\CrudController;
use App\Services\IPService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\SpaceStoreRequest;
use Modules\Space\Http\Requests\SpaceUpdateRequest;
use Modules\Space\ModuleService;
use Modules\Space\Services\SpaceService;

class SpaceController extends CrudController
{
    protected $model = Space::class;
    protected $baseRouteName = 'admin.spaces';
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

        $managedSpaces = null;
        $spaceIds = null;

        if (! $user->can('space.viewAny')) {
            $managedSpaces = $user->spaces;

            $spaceIds = $managedSpaces->pluck('id')->all();

            if ($request->parent) {

                $space = Space::select('id', '_lft', '_rgt')
                    ->find($request->parent);

                if ($user->can('manage', $space)) {
                    $spaceIds = [ $request->parent ];
                }
            }

        } elseif ($request->parent) {

            $spaceIds = [ $request->parent ];
        }

        $records = $this->spaceService->getRecords($user, $spaceIds);

        $spaceOptions = $this->spaceService->parentOptions($managedSpaces, __('Select Parent'));

        return Inertia::render('Space::SpaceIndex', $this->getData([
            'can' => [
                'add' => $user->can('create', Space::class),
                'delete' => $user->can('space.delete')
            ],
            'parent' => $request->parent,
            'parentOptions' => $spaceOptions,
            'records' => $records,
            'pageQueryParams' => (object) array_filter($request->only('term', 'parent')),
        ]));
    }

    private function instructions(): array
    {
        $extensions = implode(', ', config('constants.extensions.image'));

        $imageExtensionsText = __(
            'Accepted file extensions: :extensions.',
            [ 'extensions' => $extensions ]
        );

        $maxFileText = function ($megaBytes) {
            return __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    ($megaBytes * config('constants.one_megabyte')) * 1024
                )
            ]);
        };

        return [
            'logo' => [
                $imageExtensionsText,
                $maxFileText(5),
            ],
            'cover' => [
                $imageExtensionsText,
                $maxFileText(50),
            ],
        ];
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if ($request->parent) {
            $parentOptions = [Space::select('id', 'name as value')->find($request->parent)];
        } else {
            $managedSpaces = null;

            if (! $user->can('space.store')) {
                $managedSpaces = $user->spaces;
            }

            $parentOptions = $this->spaceService->parentOptions(
                $managedSpaces,
                $user->can('space.add') ? __("None") : null
            );
        }

        return Inertia::render('Space::SpaceCreate', $this->getData([
            'title' => 'Add New Space',
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $parentOptions,
            'typeOptions' => $this->spaceService->typeOptions(),
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'instructions' => $this->instructions(),
            'breadcrumbs' => [
                [
                    'url' => route('admin.spaces.index'),
                    'title' => 'Spaces',
                ],
            ],
        ]));
    }

    public function store(SpaceStoreRequest $request)
    {
        $space = new Space();

        $inputs = $request->validated();

        $space->saveFromInputs($inputs);

        if ($request->hasFile('logo')) {
            $this->spaceService->replaceLogo($space, $request->file('logo'));
        }

        if ($request->hasFile('cover')) {
            $this->spaceService->replaceCover($space, $request->file('cover'));
        }

        $this->generateFlashMessage('Successfully creating '.$this->title.'!');

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

        $parent = (
            $space->parent_id
            ? [
                'id' => $space->parent_id,
                'value' => $space->parent->name,
            ]
            : [
                'id' => null,
                'value' => __('None'),
            ]
        );

        $managedSpaces = null;

        if (! $user->can('space.edit')) {
            $managedSpaces = $user->spaces;
        }

        $parentOptions = $this->spaceService->parentOptionsFor(
            $space,
            $managedSpaces,
            $user->can('space.add') ? __("None") : null
        );

        $page = $space->page_id
            ? $this->spaceService->pageFormRecord($space)
            : $this->makePage();

        $canChangeParent = $user->can('changeParent', $space);

        return Inertia::render('Space::SpaceEdit', $this->getData([
            'title' => 'Edit Space',
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $canChangeParent ? $parentOptions : [$parent],
            'spaceManagers' => $this->spaceService->formattedManagers($space),
            'spaceRecord' => $this->spaceService->editableRecord($space),
            'typeOptions' => $this->spaceService->typeOptions(),
            'coverUrl' => $space->coverUrl,
            'logoUrl' => $space->logoUrl,
            'can' => [
                'page' => [
                    'read' => $user->can('managePage', Space::class),
                    'edit' => $user->can('managePage', Space::class),
                ],
                'manager' => [
                    'edit' => $user->can('manageManager', Space::class),
                ],
                'update' => $user->can('update', $space),
                'changeParent' => $canChangeParent,
            ],
            'page' => $page,
            'statusOptions' => Page::getStatusOptions(),
            'maxLength' => array_merge(
                [
                    'meta_title' => config('constants.max_length.meta_title'),
                    'meta_description' => config('constants.max_length.meta_description'),
                ],
                ModuleService::maxLengths(),
            ),
            'instructions' => $this->instructions(),
            'breadcrumbs' => [
                [
                    'url' => route('admin.spaces.index'),
                    'title' => 'Spaces',
                ],
                [
                    'title' => $space->name,
                ],
            ],
        ]));
    }

    public function update(SpaceUpdateRequest $request, Space $space)
    {
        $inputs = $request->validated();

        if (
            !empty($request->get('deleted_media')['logo'])
            && !$request->hasFile('logo')
        ) {
            $this->spaceService->deleteLogo($space);
        }

        if (
            !empty($request->get('deleted_media')['cover'])
            && !$request->hasFile('cover')
        ) {
            $this->spaceService->deleteCover($space);
        }

        if ($request->hasFile('logo')) {
            $this->spaceService->replaceLogo($space, $request->file('logo'));
        }

        if ($request->hasFile('cover')) {
            $this->spaceService->replaceCover($space, $request->file('cover'));
        }

        $space->saveFromInputs($inputs);

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return back();
    }

    public function destroy(Space $space)
    {
        $space->delete();

        $this->generateFlashMessage($this->title.' deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
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
        $this->authorize('manageManager', Space::class);

        $space->managers()->sync($request->managers);

        $this->generateFlashMessage('Manager updated successfully!');

        return back();
    }

    public function isUsedByMenus(Space $space, ?string $locale = null)
    {
        return app(MenuService::class)->isModelUsedByMenu($space, $locale);
    }
}
