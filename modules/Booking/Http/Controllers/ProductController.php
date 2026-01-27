<?php

namespace Modules\Booking\Http\Controllers;

use App\Helpers\HumanReadable;
use App\Helpers\MimeType;
use App\Http\Controllers\CrudController;
use App\Services\CountryService;
use App\Services\IPService;
use App\Services\MediaService;
use App\Services\SettingService;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Services\ProductEventService;
use Modules\Booking\Services\ProductSpaceService;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\ProductVariant;
use Modules\Ecommerce\Enums\ProductStatus;
use App\Enums\PublishingStatus;
use Modules\Ecommerce\Http\Requests\ProductRequest;
use Modules\Ecommerce\ModuleService as EcommerceModuleService;
use Modules\Ecommerce\Services\ProductService;
use Modules\Space\Entities\Space;

class ProductController extends CrudController
{
    protected $title = "booking_module::terms.product";
    protected $baseRouteName = "admin.booking.products";

    public function __construct(
        private ProductService $productService,
        private ProductEventService $productEventService,
        private ProductSpaceService $productSpaceService,
        private CountryService $countryService,
    ) {
        $this->authorizeResource(Product::class, 'product');
    }

    private function getImageMimeTypes(): Collection
    {
        return MimeType::getMimeTypes(config('constants.extensions.image'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $scopes = [
            'inStatus' => $request->status ?? null,
            'city' => $request->city ?? null,
            'country' => $request->country ?? null,
        ];

        return Inertia::render('Booking::ProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only(
                'term',
                'status',
                'city',
                'country',
            )),
            'products' => $this->productService->getRecords(
                $user,
                $request->term,
                $scopes,
            ),
            'statusOptions' => ProductStatus::options(),
            'countryOptions' => $this->productEventService->getCountryOptions(),
            'cityOptions' => $this->productEventService->getCityOptions(),
            'can' => [
                'add' => $user->can('create', Product::class),
            ],
            'i18n' => $this->translationIndexPage(),
        ]));
    }

    public function create()
    {
        $user = auth()->user();

        return Inertia::render('Booking::ProductCreate', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'can' => [
                'media' => [
                    'add' => $user->can('media.add'),
                    'browse' => $user->can('media.browse'),
                    'edit' => $user->can('media.edit'),
                    'read' => $user->can('media.read'),
                ],
                'space' => [
                    'manageProductSpace' => $user->can('manageProductSpace', Space::class),
                ],
            ],
            'title' => $this->getCreateTitle(),
            'statusOptions' => [
                [
                    'id' => 'draft',
                    'value' => 'Draft',
                ],
                [
                    'id' => 'published',
                    'value' => 'Published',
                ],
            ],
            'roleOptions' => $this->productService->roleOptions(),
            'imageMimes' => config('constants.extensions.image'),
            'rules' => [
                'maxProductFileNumber' => EcommerceModuleService::maxProductMediaNumber(),
            ],
            'instructions' => $this->getInstructions(),
            'i18n' => $this->translationCreateEditPage(),
            'dimensions' => [
                'gallery' => config('constants.dimensions.gallery'),
            ],
            'spaceOptions' => $this->productSpaceService->getSpaceOptions(),
        ]));
    }

    public function store(ProductRequest $request)
    {
        $productType = ProductType::where('name', 'Event')->first();

        $inputs = $request->all();

        $product = Product::create([
            'product_type_id' => $productType->id,
            'status' => $inputs['status'],
            'productable_type' => !empty($inputs['space_id']) ? Space::class : null,
            'productable_id' => $inputs['space_id'] ?? null,
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text($inputs['name']),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text($inputs['description']),
                ])),
                'short_description' => new TranslatedText(collect([
                    'en' => new Text($inputs['short_description']),
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

        // If a space is selected, get location from space
        $locations = [];
        if (!empty($inputs['space_id'])) {
            $space = Space::find($inputs['space_id']);
            if ($space) {
                $locations = [[
                    'city' => $space->city,
                    'country_code' => $space->country_code,
                    'latitude' => $space->latitude,
                    'longitude' => $space->longitude,
                    'address' => $space->address,
                ]];
            }
        }

        $meta = [
            'roles' => empty($inputs['roles']) ? [] : [(int) $inputs['roles']],
            'duration' => 60,
            'duration_unit' => 'minute',
            'bookable_date_range_type' => 'calendar_days_into_the_future',
            'bookable_date_range' => 60,
            'is_check_in_required' => (bool) $inputs['is_check_in_required'],
        ];

        if (!empty($locations)) {
            $meta['locations'] = $locations;
        }

        $product->setMeta($meta);
        $product->save();

        // Attach City Administrator as product manager
        $user = auth()->user();
        if ($user->hasRole('city_administrator') && !$user->can('product.edit')) {
            $user->products()->syncWithoutDetaching([$product->id]);
        }

        Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->create();

        $mediaIds = $inputs['gallery'] ?? [];

        if (! empty($mediaIds)) {
            $product->syncMedia($mediaIds);
        }

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => $this->title()
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    public function edit(Product $product)
    {
        $user = auth()->user();

        $canManageManager = $user->can('manageManager', Product::class);

        $product->load('eventSchedule.weeklyHours.times');

        $formSpace = $this->productSpaceService->formResource($product);

        // Check if product is missing location data
        $hasLocation = !empty($product->locations[0]['city'] ?? null) 
                       && !empty($product->locations[0]['country_code'] ?? null);

        return Inertia::render('Booking::ProductEdit', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'title' => $this->getEditTitle(),
            'imageMimes' => $this->getImageMimeTypes(),
            'roleOptions' => $this->productService->roleOptions(),
            'statusOptions' => $this->productService->statusOptions(),
            'product' => $this->productService->formResource($product),
            'eventDurationOptions' => $this->productEventService->durationOptions(),
            'eventStatusOptions' => PublishingStatus::options(),
            'event' => $this->productEventService->formResource($product),
            'weekdays' => $this->productEventService->weekdays()->pluck('value', 'id'),
            'weeklyHours' => $this->productEventService->weeklyHours($product),
            'dateOverrides' => $this->productEventService->dateOverrides($product),
            'pitchScheduleInfo' => $this->productEventService->pitchScheduleInfo($product),
            'geoLocation' => app(IPService::class)->getGeoLocation(),
            'defaultCountryCode' => app(IPService::class)->getCountryCode("US"),
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
            'productManagerBaseRoute' => 'admin.ecommerce.products.managers',
            'missingLocation' => !$hasLocation,
            'rules' => [
                'maxProductFileSize' => EcommerceModuleService::maxProductFileSize(),
                'maxProductFileNumber' => EcommerceModuleService::maxProductMediaNumber(),
            ],
            'managers' => (
                $canManageManager
                ? $this->productService->formattedManagers($product)
                : []
            ),
            'can' => [
                'productManager' => [
                    'edit' => $canManageManager,
                ],
                'media' => [
                    'add' => $user->can('media.add'),
                    'browse' => $user->can('media.browse'),
                    'edit' => $user->can('media.edit'),
                    'read' => $user->can('media.read'),
                ],
                'space' => [
                    'manageProductSpace' => $user->can('manageProductSpace', Space::class),
                ],
            ],
            'instructions' => $this->getInstructions(),
            'i18n' => $this->translationCreateEditPage(),
            'dimensions' => [
                'gallery' => config('constants.dimensions.gallery'),
            ],
            'space' => $formSpace,
            'spaceOptions' => $this->productSpaceService->getSpaceOptions($formSpace['id']),
        ]));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $inputs = $request->all();

        $product->attribute_data = [
            'name' => new TranslatedText(collect([
                'en' => new Text($inputs['name']),
            ])),
            'description' => new TranslatedText(collect([
                'en' => new Text($inputs['description']),
            ])),
            'short_description' => new TranslatedText(collect([
                'en' => new Text($inputs['short_description']),
            ])),
        ];

        $product->status = $inputs['status'];

        $product->setMeta([
            'roles' => empty($inputs['roles']) ? [] : [(int) $inputs['roles']],
            'is_check_in_required' => (bool) $inputs['is_check_in_required'],
        ]);

        $product->save();

        $mediaIds = $inputs['gallery'] ?? [];

        if (! empty($mediaIds)) {
            $product->syncMedia($mediaIds);
        }

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title()
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();

        $product->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => $this->title()
        ]);

        $user->load('products');

        return redirect()->route(
            $user->can('viewAny', Product::class)
            ? $this->baseRouteName.'.index'
            : 'admin.dashboard'
        );
    }

    private function getInstructions(): array
    {
        return [
            'mediaLibrary' => [
                __('Accepted file extensions: :extensions.', [
                    'extensions' => implode(', ', config('constants.extensions.image'))
                ]),
                __('Max file size: :filesize per file.', [
                    'filesize' => HumanReadable::bytesToHuman(
                        SettingService::maxFileSize() * 1024
                    )
                ]),
                __('Recommended dimension: :dimension.', [
                    'dimension' => config('constants.recomended_dimensions.gallery')
                ])
            ],
        ];
    }

    private function translationIndexPage(): array
    {
        return [
            'search' => __('Search'),
            'filter' => __('Filter'),
            'status' => __('Status'),
            'create_new' => __('Create new'),
            'name' => __('Name'),
            'city' => __('City'),
            'country' => __('Country'),
            'status' => __('Status'),
            'actions' => __('Actions'),
            'are_you_sure' => __('Are you sure?'),
        ];
    }

    private function translationCreateEditPage(): array
    {
        return [
            ...[
                'details' => __('Details'),
                'name' => __('Name'),
                'short_description' => __('Short description'),
                'description' => __('Description'),
                'status' => __('Status'),
                'check_in_required' => __('Is a check-in required?'),
                'visibility' => __('Visibility'),
                'roles' => __('Roles'),
                'select' => __('Select'),
                'gallery' => __('Gallery'),
                'upload' => __('Upload'),
                'cancel' => __('Cancel'),
                'create' => __('Create'),
                'update' => __('Update'),
                'product' => __('booking_module::terms.product'),
                'event' => __('booking_module::terms.'),
                'manager' => __('Manager'),
                'duration' => __('Duration'),
                'bookable_date_range' => __('Bookable date range (Calendar days into the future)'),
                'pitch_date_range' => __('Pitch date range'),
                'pitch_timezone' => __('Pitch timezone'),
                'address' => __('Address'),
                'city' => __('City'),
                'country' => __('Country'),
                'latitude' => __('Latitude'),
                'longitude' => __('Longitude'),
                'schedule' => __('Schedule'),
                'timezone' => __('Timezone'),
                'weekly_hours' => __('Weekly hours'),
                'date_override' => __('Date override'),
                'date_override_description' => __('Add dates when your availability changes from your weekly hours'),
                'add_date' => __('Add :resource', ['resource' => __('Date')]),
                'map' => __('Map'),
                'unavailable' => __('Unavailable'),
                'choose_product_manager' => __('Choose pitch manager'),
                'space' => __('space_module::terms.space'),
                'select_space' => __('Select location'),
                'select_space_note' => __('The :resource can only have one location.', ['resource' => __('booking_module::terms.product')]),
                'tips' => [
                    'pitch_date_range' => __('The overall date range (start and end dates only) when this pitch is available. No bookings can be made outside these dates. The specific booking times are set in the Schedule section below.'),
                    'schedule' => __('Set the specific days and times when bookings can be made within the Pitch Date Range above. Configure weekly hours for regular availability and date overrides for special dates or closures.'),
                    'timezone' => __('Select your timezone to ensure that all scheduled events and time-related information are accurate.'),
                    'weekly_hours' => __('Specify the available event hours that can be booked by performers on a weekly basis.'),
                    'date_override' => __('Use this field to manually select a specific date, overriding the weekly event hours.'),
                ],
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
