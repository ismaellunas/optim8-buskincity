<?php

namespace Modules\Booking\Http\Controllers;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Helpers\MimeType;
use App\Http\Controllers\CrudController;
use App\Models\Media;
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
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\ProductVariant;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Ecommerce\Http\Requests\ProductRequest;
use Modules\Ecommerce\ModuleService as EcommerceModuleService;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "Product";
    protected $baseRouteName = "admin.booking.products";

    private $productService;
    private $mediaService;
    private $productEventService;

    public function __construct(
        ProductService $productService,
        MediaService $mediaService,
        ProductEventService $productEventService
    ) {
        $this->authorizeResource(Product::class, 'product');

        $this->productService = $productService;
        $this->mediaService = $mediaService;
        $this->productEventService = $productEventService;
    }

    private function getImageMimeTypes(): Collection
    {
        return MimeType::getMimeTypes(config('constants.extensions.image'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Booking::ProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only('term', 'status')),
            'products' => $this->productService->getRecords(
                $user,
                $request->term,
                ['inStatus' => $request->status ?? null]
            ),
            'statusOptions' => ProductStatus::options(),
            'can' => [
                'add' => $user->can('product.add'),
            ],
        ]));
    }

    public function create()
    {
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
        ]));
    }

    public function store(ProductRequest $request)
    {
        $productType = ProductType::where('name', 'Event')->first();

        $inputs = $request->all();

        $product = Product::create([
            'product_type_id' => $productType->id,
            'status' => $inputs['status'],
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

        $meta = [
            'roles' => empty($inputs['roles']) ? [] : [(int) $inputs['roles']],
            'duration' => 30,
            'duration_unit' => 'minute',
            'bookable_date_range_type' => 'calendar_days_into_the_future',
            'bookable_date_range' => 60,
            'is_check_in_required' => (bool) $inputs['is_check_in_required'],
        ];

        $product->setMeta($meta);
        $product->save();

        Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->create();

        $mediaIds = $inputs['gallery'] ?? [];

        if (! empty($mediaIds)) {
            $product->syncMedia($mediaIds);
        }

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    public function edit(Product $product)
    {
        $canManageManager = auth()->user()->can('manageManager', Product::class);

        $product->load('eventSchedule.weeklyHours.times');

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
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'roleOptions' => $this->productService->roleOptions(),
            'statusOptions' => $this->productService->statusOptions(),
            'product' => $this->productService->formResource($product),
            'eventDurationOptions' => $this->productEventService->durationOptions(),
            'event' => $this->productEventService->formResource($product),
            'timezoneOptions' => $this->productEventService->timezoneOptions(),
            'weekdays' => $this->productEventService->weekdays()->pluck('value', 'id'),
            'weeklyHours' => $this->productEventService->weeklyHours($product),
            'dateOverrides' => $this->productEventService->dateOverrides($product),
            'geoLocation' => app(IPService::class)->getGeoLocation(),
            'defaultCountryCode' => app(IPService::class)->getCountryCode("US"),
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
            'productManagerBaseRoute' => 'admin.ecommerce.products.managers',
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
                ]
            ],
            'instructions' => $this->getInstructions(),
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

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();

        $product->delete();

        $this->generateFlashMessage($this->title.' deleted successfully!');

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
            'mediaLibrary' => MediaService::defaultMediaLibraryInstructions(),
        ];
    }
}
