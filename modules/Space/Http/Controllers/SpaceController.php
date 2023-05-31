<?php

namespace Modules\Space\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\Media;
use App\Services\IPService;
use App\Services\MediaService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\SpaceIndexRequest;
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

    public function index(SpaceIndexRequest $request)
    {
        $user = auth()->user();
        $managedSpaces = null;
        $spaceIds = null;

        $scopes = [
            'search' => $request->term,
            'inType' => $request->types,
        ];
        $scopes = array_filter($scopes);

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

        $records = $this->spaceService->getRecords($user, $spaceIds, $scopes);

        $spaceOptions = $this->spaceService->parentOptions($managedSpaces, __('Select Parent'));

        return Inertia::render('Space::SpaceIndex', $this->getData([
            'can' => [
                'add' => $user->can('create', Space::class),
                'delete' => $user->can('space.delete')
            ],
            'parent' => $request->parent,
            'parentOptions' => $spaceOptions,
            'records' => $records,
            'pageQueryParams' => (object) array_filter($request->only('term', 'parent', 'types')),
            'typeOptions' => $this->spaceService->typeOptions(),
            'i18n' => [
                'search' => __('Search'),
                'select_parent' => __('Select parent'),
                'type' => __('Type'),
                'create_new' => __('Create new'),
                'name' => __('Name'),
                'parents' => __('Parents'),
                'type' => __('Type'),
                'actions' => __('Actions'),
                'are_you_sure' => __('Are you sure?'),
            ],
        ]));
    }

    private function instructions(): array
    {
        return [
            'logoMediaLibrary' => MediaService::logoMediaLibraryInstructions(),
            'coverMediaLibrary' => [
                ...MediaService::defaultMediaLibraryInstructions(),
                ...[
                    __('Recommended dimension: :dimension.', [ 'dimension' => config('constants.dimensions.cover') ]),
                ]
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
            'title' => $this->getCreateTitle(),
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $parentOptions,
            'typeOptions' => $this->spaceService->typeOptions(__('None')),
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'instructions' => $this->instructions(),
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    public function store(SpaceStoreRequest $request)
    {
        $space = new Space();

        $inputs = $request->validated();

        $space->saveFromInputs($inputs);

        if ($request->has('logo')) {
            $this->spaceService->replaceLogo($space, $request->logo);
        }

        if ($request->has('cover')) {
            $this->spaceService->replaceCover($space, $request->cover);
        }

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __($this->title)
        ]);

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

        $coverMedia = $space->cover;
        if ($coverMedia) {
            $this->transformMedia($coverMedia);
        }

        $logoMedia = $space->logo;
        if ($logoMedia) {
            $this->transformMedia($logoMedia);
        }

        return Inertia::render('Space::SpaceEdit', $this->getData([
            'title' => $this->getEditTitle(),
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $canChangeParent ? $parentOptions : [$parent],
            'spaceManagers' => $this->spaceService->formattedManagers($space),
            'spaceRecord' => $this->spaceService->editableRecord($space),
            'typeOptions' => $this->spaceService->typeOptions(__('None')),
            'coverMedia' => $coverMedia,
            'logoMedia' => $logoMedia,
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
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    public function update(SpaceUpdateRequest $request, Space $space)
    {
        $inputs = $request->validated();

        if ($request->has('logo')) {
            $this->spaceService->replaceLogo($space, $request->logo);
        }

        if ($request->has('cover')) {
            $this->spaceService->replaceCover($space, $request->cover);
        }

        $space->saveFromInputs($inputs);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __($this->title)
        ]);

        return back();
    }

    public function destroy(Space $space)
    {
        $space->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __($this->title)
        ]);

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

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Manager')
        ]);

        return back();
    }

    public function isUsedByMenus(Space $space, ?string $locale = null)
    {
        return app(MenuService::class)->isModelUsedByMenu($space, $locale);
    }

    private function transformMedia(Media $media): void
    {
        $media->append(['is_image', 'thumbnail_url', 'display_file_name']);
    }

    private function translationCreateEditPage(): array
    {
        return [
            ...[
                'space' => __('Space'),
                'event' => __('Event'),
                'manager' => __('Manager'),
                'page' => __('Page'),
                'name' => __('Name'),
                'parent' => __('Parent'),
                'type' => __('Type'),
                'address' => __('Address'),
                'latitude' => __('Latitude'),
                'longitude' => __('Longitude'),
                'logo' => __('Logo'),
                'cover' => __('Cover'),
                'contacts' => __('Contacts'),
                'add_contact' => __('Add contact'),
                'contact' => __('Contact'),
                'email' => __('Email'),
                'phone' => __('Phone'),
                'cancel' => __('Cancel'),
                'create' => __('Create'),
                'update' => __('Update'),
                'save' => __('Save'),
                'is_page_enabled' => __('Is page enabled?'),
                'description' => __('Description'),
                'condition' => __('Condition'),
                'surface' => __('Surface'),
                'add_new' => __('Add new'),
                'title' => __('Title'),
                'started_at' => __('Started at'),
                'ended_at' => __('Ended at'),
                'actions' => __('Actions'),
                'add_new_event' => __('Add :resource', ['resource' => __('New event')]),
                'edit_event' => __('Edit :resource', ['resource' => __('Event')]),
                'started_and_ended_at' => __('Started at and ended at'),
                'description' => __('Description'),
                'are_you_sure' => __('Are you sure?'),
                'choose_manager' => __('Choose manager'),
                'details' => __('Details'),
                'slug' => __('Slug'),
                'status' => __('Status'),
                'excerpt' => __('Excerpt'),
                'meta_title' => __('Meta title'),
                'meta_description' => __('Meta description'),
                'page_preview' => __('Page preview'),
                'remove_page_confirmation' => __('This action will also remove the page on the navigation menu.'),
                'yes' => __('Yes'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
