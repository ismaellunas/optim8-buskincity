<?php

namespace Modules\Booking\Http\Controllers;

use App\Helpers\HumanReadable;
use App\Helpers\MimeType;
use App\Http\Controllers\CrudController;
use App\Models\City;
use App\Services\CityService;
use App\Services\IPService;
use App\Services\LocationService;
use App\Services\MediaService;
use App\Services\SettingService;
use App\Services\UserScopeService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Http\Requests\ProductPitchRequest;
use Modules\Booking\Services\PitchBookingService;
use Modules\Booking\Services\ProductSpaceService;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\ProductVariant;
use Modules\Ecommerce\Enums\ProductStatus;
use App\Enums\PublishingStatus;
use Modules\Ecommerce\ModuleService as EcommerceModuleService;
use Modules\Ecommerce\Services\ProductService;
use Modules\Space\Entities\Space;

class ProductController extends CrudController
{
    protected $title = "booking_module::terms.product";
    protected $baseRouteName = "admin.booking.products";

    public function __construct(
        private ProductService $productService,
        private PitchBookingService $pitchBookingService,
        private ProductSpaceService $productSpaceService,
        private CityService $cityService,
        private LocationService $locationService,
        private UserScopeService $userScopeService,
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
            'countryOptions' => $this->pitchBookingService->getAdminFilterCountryOptions($user),
            'cityOptions' => $this->pitchBookingService->getAdminFilterCityOptions($user),
            'can' => [
                'add' => $user->can('create', Product::class),
            ],
            'i18n' => $this->translationIndexPage(),
        ]));
    }

    public function create()
    {
        $user = auth()->user();
        $isSpecialEventPitch = $this->pitchBookingService->requiresFourteenDayBookableWindow();
        $scopedCities = $this->scopedCitiesForPitchForm();

        // Avoid showing validation errors from a previous failed save on a fresh create form.
        session()->forget('errors');

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
            'eventDurationOptions' => $this->pitchBookingService->durationOptions(),
            'weekdays' => $this->pitchBookingService->weekdays()->pluck('value', 'id'),
            'weeklyHours' => $this->pitchBookingService->defaultWeeklyHours(),
            'defaultCountryCode' => $scopedCities[0]['country_code']
                ?? app(IPService::class)->getCountryCode('US'),
            'defaultTimezone' => app(IPService::class)->getTimezone(),
            'geoLocation' => app(IPService::class)->getGeoLocation(),
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
            'isSpecialEventPitch' => $isSpecialEventPitch,
            'maxPitchDateSpanDays' => $isSpecialEventPitch ? 14 : null,
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
            'scopedCities' => $scopedCities,
            'requiresSavedLocation' => $this->userScopeService->requiresSavedLocationForPitch($user),
            'isSpecialEventsAdmin' => $user->isSpecialEventsAdmin(),
        ]));
    }

    public function store(ProductPitchRequest $request)
    {
        $inputs = $request->validated();
        $user = auth()->user();
        $location = $this->resolveLocationPayload($inputs);

        $product = DB::transaction(function () use ($inputs, $user, $location) {
            $productType = ProductType::where('name', 'Event')->first();

            $product = Product::create([
                'product_type_id' => $productType->id,
                'status' => $inputs['status'],
                'productable_type' => ! empty($inputs['space_id'] ?? null) ? Space::class : null,
                'productable_id' => ! empty($inputs['space_id'] ?? null) ? (int) $inputs['space_id'] : null,
                'is_special_event' => $user->isSpecialEventsAdmin(),
                'attribute_data' => [
                    'name' => new TranslatedText(collect([
                        'en' => new Text($inputs['name']),
                    ])),
                    'description' => new TranslatedText(collect([
                        'en' => new Text($inputs['description'] ?? ''),
                    ])),
                    'short_description' => new TranslatedText(collect([
                        'en' => new Text($inputs['short_description'] ?? ''),
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

            $this->persistPitchBookingData($product, $inputs, $location);

            return $product;
        });

        if (
            ($user->isCityAdministrator() || $user->isSpecialEventsAdmin())
            && ! $user->can('product.edit')
        ) {
            $user->products()->syncWithoutDetaching([$product->id]);
        }

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => $this->title(),
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

        $isSpecialEventPitch = $this->pitchBookingService->requiresFourteenDayBookableWindow($product);

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
            'eventDurationOptions' => $this->pitchBookingService->durationOptions(),
            'eventStatusOptions' => PublishingStatus::options(),
            'event' => $this->pitchBookingService->formResource($product),
            'weekdays' => $this->pitchBookingService->weekdays()->pluck('value', 'id'),
            'weeklyHours' => $this->pitchBookingService->weeklyHours($product),
            'dateOverrides' => $this->pitchBookingService->dateOverrides($product),
            'pitchScheduleInfo' => $this->pitchBookingService->pitchScheduleInfo($product),
            'geoLocation' => app(IPService::class)->getGeoLocation(),
            'defaultCountryCode' => app(IPService::class)->getCountryCode("US"),
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
            'productManagerBaseRoute' => 'admin.ecommerce.products.managers',
            'missingLocation' => !$hasLocation,
            'isSpecialEventPitch' => $isSpecialEventPitch,
            'maxPitchDateSpanDays' => $isSpecialEventPitch ? 14 : null,
            'scopedCities' => $this->scopedCitiesForPitchForm(),
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
            'requiresSavedLocation' => $this->userScopeService->requiresSavedLocationForPitch($user),
            'isSpecialEventsAdmin' => $user->isSpecialEventsAdmin(),
        ]));
    }

    public function update(ProductPitchRequest $request, Product $product)
    {
        $inputs = $request->validated();
        $location = $this->resolveLocationPayload($inputs);

        DB::transaction(function () use ($inputs, $product, $location) {
            $this->persistPitchBookingData($product, $inputs, $location);
        });

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title(),
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    private function resolveLocationPayload(array $inputs): array
    {
        $location = collect($inputs['location'])->only([
            'address', 'city', 'country_code',
        ])->all();

        $latitude = data_get($inputs, 'location.latitude');
        $longitude = data_get($inputs, 'location.longitude');

        $location['latitude'] = ! is_null($latitude) ? (float) $latitude : null;
        $location['longitude'] = ! is_null($longitude) ? (float) $longitude : null;
        $location['city'] = $this->normalizeLocationCityName(
            $location['city'] ?? null,
            $inputs['city_id'] ?? null
        );

        if ($location['latitude'] && $location['longitude']) {
            $response = $this->getReversedGeocoding($location['latitude'], $location['longitude']);

            if ($response->ok() && $response->json()['status'] !== 'REQUEST_DENIED') {
                $location['geocode'] = $response->json()['results'][0];
            }
        }

        return $location;
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $location
     */
    private function resolvePitchCity(array $inputs, array $location): ?City
    {
        if (! empty($inputs['city_id'])) {
            return City::query()->find((int) $inputs['city_id']);
        }

        $cityName = $this->normalizeLocationCityName(
            $location['city'] ?? null,
            null
        );
        $countryCode = $location['country_code'] ?? null;

        if ($cityName === null || empty($countryCode)) {
            return null;
        }

        return $this->cityService->findOrCreate(
            $cityName,
            (string) $countryCode,
            $location['latitude'],
            $location['longitude']
        );
    }

    private function normalizeLocationCityName(mixed $city, ?int $cityId): ?string
    {
        if (is_string($city) && $city !== '') {
            return $city;
        }

        if (is_array($city) && ! empty($city['name'])) {
            return (string) $city['name'];
        }

        if ($city instanceof City) {
            return $city->name;
        }

        if ($cityId) {
            return City::query()->whereKey($cityId)->value('name');
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $location
     */
    private function persistPitchBookingData(Product $product, array $inputs, array $location): void
    {
        $product->attribute_data = [
            'name' => new TranslatedText(collect([
                'en' => new Text($inputs['name']),
            ])),
            'description' => new TranslatedText(collect([
                'en' => new Text($inputs['description'] ?? ''),
            ])),
            'short_description' => new TranslatedText(collect([
                'en' => new Text($inputs['short_description'] ?? ''),
            ])),
        ];

        $product->status = $inputs['status'];
        $product->duration = $inputs['duration'];
        $product->duration_unit = 'minute';
        $product->bookable_date_range = $inputs['bookable_date_range'];

        if (! empty($inputs['space_id'] ?? null)) {
            $product->productable_type = Space::class;
            $product->productable_id = (int) $inputs['space_id'];
        }

        $product->setMeta([
            'roles' => empty($inputs['roles']) ? [] : [(int) $inputs['roles']],
            'is_check_in_required' => (bool) $inputs['is_check_in_required'],
            'pitch_started_at' => $inputs['pitch_started_at'],
            'pitch_ended_at' => $inputs['pitch_ended_at'],
            'pitch_timezone' => $inputs['timezone'],
        ]);

        $city = $this->resolvePitchCity($inputs, $location);

        if ($city) {
            $this->userScopeService->assertCityInScope($city->id);

            $locationModel = $this->locationService->findOrCreateFromPitchData(
                $city,
                $location,
                ! empty($inputs['space_id'] ?? null)
                    ? (int) $inputs['space_id']
                    : (
                        $product->productable_type === Space::class
                            ? $product->productable_id
                            : null
                    )
            );

            $product->city_id = $city->id;
            $product->location_id = $locationModel->id;
            $location['city'] = $city->name;
        }

        $product->locations = [$location];
        $product->save();

        $mediaIds = $inputs['gallery'] ?? [];
        if (! empty($mediaIds)) {
            $product->syncMedia($mediaIds);
        } else {
            $product->detachGallery();
        }

        $schedule = $product->eventSchedule ?? Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->make();

        $schedule->timezone = $inputs['timezone'];
        $schedule->save();

        $this->pitchBookingService->saveWeeklyHours($inputs['weekly_hours'] ?? [], $schedule);
        $this->pitchBookingService->saveDateOverrides(
            collect($inputs['date_overrides'] ?? []),
            $schedule
        );
    }

    /**
     * Scoped city list for pitch location pickers (FR-PITCH-4).
     * Empty array = unrestricted API search (global admins and unscoped roles).
     *
     * @return array<int, array{id: int, name: string, country_code: string, latitude: float|null, longitude: float|null}>
     */
    private function scopedCitiesForPitchForm(): array
    {
        if ($this->userScopeService->isGloballyScoped()) {
            return [];
        }

        $cityIds = $this->userScopeService->scopedCityIds();

        if ($cityIds === []) {
            return [];
        }

        return $this->userScopeService->scopedCityOptions()
            ->map(fn ($city) => [
                'id' => $city->id,
                'name' => $city->name,
                'country_code' => $city->country_code,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ])
            ->values()
            ->all();
    }

    private function getReversedGeocoding(float $latitude, float $longitude): Response
    {
        return Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $latitude . ',' . $longitude,
            'key' => app(SettingService::class)->getGoogleApi(),
        ]);
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
                'create_blocked_title' => __('A saved location is required'),
                'product' => __('booking_module::terms.product'),
                'event' => __('booking_module::terms.'),
                'manager' => __('Manager'),
                'duration' => __('Timeslot duration'),
                'bookable_date_range' => __('Bookable date range (Calendar days into the future)'),
                'pitch_date_range' => __('Pitch date range'),
                'address' => __('Address'),
                'city' => __('City'),
                'country' => __('Country'),
                'latitude' => __('Latitude'),
                'longitude' => __('Longitude'),
                'schedule' => __('Schedule'),
                'timezone' => __('Timezone'),
                'weekly_hours' => __('Weekly days and hours'),
                'date_override' => __('Date override'),
                'date_override_description' => __('Add dates when your availability changes from your weekly days and hours'),
                'add_date' => __('Add :resource', ['resource' => __('Date')]),
                'map' => __('Map'),
                'unavailable' => __('Unavailable'),
                'choose_product_manager' => __('Choose pitch manager'),
                'space' => __('space_module::terms.space'),
                'select_space' => __('Select location'),
                'select_space_note' => __('Multiple pitches may share a location when their date ranges do not overlap.'),
                'tips' => [
                    'pitch_date_range' => __('The overall date range (start and end dates only) when this pitch is available. No bookings can be made outside these dates. The specific booking times are set in the Schedule section below.'),
                    'bookable_date_range_derived' => __('Set automatically from the pitch date range above.'),
                    'pitch_location_from_space' => __('Location details are taken from your selected saved location.'),
                    'no_saved_locations' => __('Before you can create a pitch, add a location under Locations, then return here to select it.'),
                    'no_saved_locations_contact_city_admin' => __('Before you can create a special event pitch, a location must exist in your city. Contact your City Administrator to add one, then return here to create your pitch.'),
                    'special_event_date_range' => __('Special event pitches may have a bookable window of up to 14 days. The pitch remains visible year-round, but bookings are only accepted within this window.'),
                    'pitch_date_range_exceeds_max' => __('Special event pitches may span at most :days days (currently :span days).'),
                    'schedule' => __('Set the specific days and times when bookings can be made within the Pitch Date Range above. Configure weekly days and hours for regular availability and date overrides for special dates or closures.'),
                    'timezone' => __('Select your timezone to ensure that all scheduled events and time-related information are accurate.'),
                    'weekly_hours' => __('Use this option to manually override day(s)/hours within the pitch date range.'),
                    'date_override' => __('Use this field to manually select a specific date, overriding the weekly days and hours.'),
                ],
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
