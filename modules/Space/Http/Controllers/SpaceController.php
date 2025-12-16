<?php

namespace Modules\Space\Http\Controllers;

use App\Enums\PublishingStatus;
use App\Http\Controllers\CrudController;
use App\Services\IPService;
use App\Services\MediaService;
use App\Services\MenuService;
use App\Services\SettingService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Modules\Booking\Entities\Schedule;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\ProductVariant;
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
    protected $title = "space_module::terms.space";

    public function __construct(
        private SpaceService $spaceService,
        private IPService $ipService
    ) {
        $this->authorizeResource(Space::class, 'space');
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

        if ($user->hasRole('city_administrator')) {
            // City administrators should see both:
            // 1. All spaces they manage (created by them)
            // 2. All spaces in their administered cities
            $managedSpaces = $user->spaces;
            $managedSpaceIds = $managedSpaces->pluck('id')->all();
            
            // Get all space IDs from their cities
            $cityIds = $user->adminCities->pluck('id')->toArray();
            $citySpaceIds = Space::whereIn('city_id', $cityIds)
                ->pluck('id')
                ->all();
            
            // Combine both sets of IDs (spaces they manage + spaces in their cities)
            $spaceIds = array_unique(array_merge($managedSpaceIds, $citySpaceIds));
        } elseif (! $user->can('space.viewAny')) {
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
            'title' => $this->getIndexTitle(),
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
                    __('Recommended dimension: :dimension.', [ 'dimension' => config('constants.recomended_dimensions.cover') ]),
                ]
            ],
        ];
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $isCityAdmin = $user->hasRole('city_administrator');

        // For City Administrators, use restricted options
        if ($isCityAdmin) {
            $this->spaceService->ensureCitySpacesExist($user);
            $parentOptions = $this->spaceService->cityAdminParentOptions($user);
            $typeOptions = $this->spaceService->cityAdminTypeOptions();
            $userCities = $user->adminCities->map(fn($city) => [
                'id' => $city->id,
                'name' => $city->name,
                'country_code' => $city->country_code,
            ]);
        } else {
            // Existing logic for other users
            if ($request->parent) {
                $parentSpace = Space::select('id', 'name as value')->find($request->parent);
                $parentOptions = $parentSpace ? collect([$parentSpace]) : collect();
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
            $typeOptions = $this->spaceService->typeOptions(__('None'));
            $userCities = collect();
        }

        Inertia::share('googleApiKey', app(SettingService::class)->getGoogleApi());
        Inertia::share('geoLocation', app(IPService::class)->getGeoLocation());

        // Get product-related options for optional product creation (only if Booking module is enabled)
        $isBookingEnabled = \Nwidart\Modules\Facades\Module::has('Booking') && \Nwidart\Modules\Facades\Module::isEnabled('Booking');
        $canCreateProduct = $isBookingEnabled && $user->can('product.add');
        
        $roleOptions = collect()
            ->push(['id' => null, 'value' => '- '.__('Select').' -'])
            ->merge(app(UserService::class)->getRoleOptions());

        return Inertia::render('Space::SpaceCreate', $this->getData([
            'title' => $this->getCreateTitle(),
            'defaultCountry' => app(IPService::class)->getCountryCode(),
            'parentOptions' => $parentOptions,
            'typeOptions' => $typeOptions,
            'isCityAdmin' => $isCityAdmin,
            'userCities' => $userCities,
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
            'can' => [
                'media' => [
                    'add' => $user->can('media.add'),
                    'browse' => $user->can('media.browse'),
                    'edit' => $user->can('media.edit'),
                    'read' => $user->can('media.read'),
                ],
                'createProduct' => $canCreateProduct,
            ],
            'dimensions' => [
                'logo' => config('constants.dimensions.logo'),
                'cover' => config('constants.dimensions.cover'),
            ],
            // Product creation options
            'productRoleOptions' => $roleOptions,
            'productStatusOptions' => [
                ['id' => 'draft', 'value' => 'Draft'],
                ['id' => 'published', 'value' => 'Published'],
            ],
        ]));
    }

    public function store(SpaceStoreRequest $request)
    {
        $space = new Space();

        $inputs = $request->validated();

        $space->saveFromInputs($inputs);

        // Attach City Administrator to the space they created
        $user = auth()->user();
        if ($user->hasRole('city_administrator') && !$user->can('space.edit')) {
            $user->spaces()->syncWithoutDetaching([$space->id]);
        }

        if ($request->has('logo')) {
            $this->spaceService->replaceLogo($space, $request->logo);
        }

        if ($request->has('cover')) {
            $this->spaceService->replaceCover($space, $request->cover);
        }

        // Create Product if requested and Booking module is enabled
        if ($request->input('create_product', false) && \Nwidart\Modules\Facades\Module::has('Booking') && \Nwidart\Modules\Facades\Module::isEnabled('Booking')) {
            $this->createProductForSpace($space, $request);
        }

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => $this->title()
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $space->id);
    }

    /**
     * Create a Product linked to the Space
     */
    private function createProductForSpace(Space $space, SpaceStoreRequest $request): void
    {
        $productType = ProductType::where('name', 'Event')->first();

        if (!$productType) {
            return;
        }

        $product = Product::create([
            'product_type_id' => $productType->id,
            'status' => $request->input('product_status', 'draft'),
            'productable_type' => Space::class,
            'productable_id' => $space->id,
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text($request->input('product_name')),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text($request->input('product_description', '')),
                ])),
                'short_description' => new TranslatedText(collect([
                    'en' => new Text($request->input('product_short_description', '')),
                ])),
            ],
        ]);

        $taxClass = TaxClass::getDefault();

        ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => $taxClass->id,
            'purchasable' => 'always',
            'shippable' => false,
            'stock' => 0,
            'backorder' => 0,
            'sku' => 'EVENT-'.$product->id,
        ]);

        // Set product metadata including location from space
        $meta = [
            'roles' => empty($request->input('product_roles')) ? [] : [(int) $request->input('product_roles')],
            'duration' => 60,
            'duration_unit' => 'minute',
            'bookable_date_range_type' => 'calendar_days_into_the_future',
            'bookable_date_range' => 60,
            'is_check_in_required' => (bool) $request->input('product_is_check_in_required', false),
            'locations' => [[
                'city' => $space->city,
                'country_code' => $space->country_code,
                'latitude' => $space->latitude,
                'longitude' => $space->longitude,
                'address' => $space->address,
            ]],
        ];

        $product->setMeta($meta);
        $product->save();

        // Attach City Administrator as product manager
        $user = auth()->user();
        if ($user->hasRole('city_administrator') && !$user->can('product.edit')) {
            $user->products()->syncWithoutDetaching([$product->id]);
        }

        // Create schedule for the product
        Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->create();
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
            $coverMedia->transformMediaLibrary();
        }

        $logoMedia = $space->logo;
        if ($logoMedia) {
            $logoMedia->transformMediaLibrary();
        }

        Inertia::share('googleApiKey', app(SettingService::class)->getGoogleApi());
        Inertia::share('geoLocation', app(IPService::class)->getGeoLocation());
        Inertia::share('timezone', function () {
            $timezone = $this->ipService->getTimezone();
            return ($timezone == 'ETC/UTC') ? 'UTC' : $timezone;
        });

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
                'media' => [
                    'add' => $user->can('media.add'),
                    'browse' => $user->can('media.browse'),
                    'edit' => $user->can('media.edit'),
                    'read' => $user->can('media.read'),
                ],
            ],
            'page' => $page,
            'statusOptions' => Page::getStatusOptions(),
            'eventStatusOptions' => PublishingStatus::options(),
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
            'dimensions' => [
                'logo' => config('constants.dimensions.logo'),
                'cover' => config('constants.dimensions.cover'),
            ],
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
            'resource' => $this->title()
        ]);

        return back();
    }

    public function destroy(Space $space)
    {
        $space->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => $this->title()
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

    private function translationCreateEditPage(): array
    {
        return [
            ...[
                'space' => __('space_module::terms.space'),
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
                'yes' => __('Yes'),
                'timezone' => __('Timezone'),
                'is_same_address_as_parent' => __("Is it the same address as the parent?"),
                'affected_menu_warning' => __('This page update may affect the navigation menu on the Theme Header and Footer.'),
                // Product creation translations
                'create_product' => __('Create a bookable product for this space'),
                'product_details' => __('Product Details'),
                'product_name' => __('Product Name'),
                'product_description' => __('Product Description'),
                'product_short_description' => __('Product Short Description'),
                'product_status' => __('Product Status'),
                'product_roles' => __('Visible to Role'),
                'product_check_in_required' => __('Is check-in required?'),
                'product_note' => __('The product will be automatically linked to this space and will inherit the space\'s location details.'),
                'tips' => [
                    'timezone' => __('Select your timezone to ensure that all scheduled events and time-related information are accurate.'),
                ]
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
