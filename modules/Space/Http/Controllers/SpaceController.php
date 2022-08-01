<?php

namespace Modules\Space\Http\Controllers;

use App\Helpers\HumanReadable;
use App\Http\Controllers\CrudController;
use App\Services\CountryService;
use App\Services\IPService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\SpaceRequest;
use Modules\Space\ModuleService;
use Modules\Space\Services\SpaceService;

class SpaceController extends CrudController
{
    protected $model = Space::class;
    protected $baseRouteName = 'admin.spaces';
    protected $title = "Space";

    private $spaceService;

    public function __construct(
        CountryService $countryService,
        SpaceService $spaceService
    ) {
        $this->authorizeResource(Space::class, 'space');

        $this->countryService = $countryService;
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
            $parentOptions = $this->spaceService->parentOptions(
                $user,
                $user->can('space.create')
            );
        }

        return Inertia::render('Space::SpaceCreate', $this->getData([
            'title' => 'Add New Space',
            'countryOptions' => $this->countryService ->getPhoneCountryOptions(),
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $parentOptions,
            'typeOptions' => $this->spaceService->typeOptions(),
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'instructions' => $this->instructions(),
        ]));
    }

    public function store(SpaceRequest $request)
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
            : null
        );

        $page = $space->page;

        if (! $page) {
            $page = $this->makePage();
        } else {
            $page->load('translations');
        }

        return Inertia::render('Space::SpaceEdit', $this->getData([
            'title' => 'Edit Space',
            'countryOptions' => $this->countryService->getPhoneCountryOptions(),
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $parent ? [$parent] : [],
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
                ]
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
        ]));
    }

    public function update(SpaceRequest $request, Space $space)
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

    public function destroy(Request $request, Space $space)
    {
        $space->delete();

        $this->generateFlashMessage($this->title.' deleted successfully!');

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
        $this->authorize('manageManager', Space::class);

        $space->managers()->sync($request->managers);

        $this->generateFlashMessage('Manager updated successfully!');

        return back();
    }
}
