<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Http\Controllers\CrudController;
use App\Models\Media;
use App\Services\MediaService;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\Schedule;
use Modules\Ecommerce\Http\Requests\ProductCreateRequest;
use Modules\Ecommerce\Http\Requests\ProductUpdateRequest;
use Modules\Ecommerce\ModuleService;
use Modules\Ecommerce\Services\ProductEventService;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "Product";
    protected $baseRouteName = "admin.ecommerce.products";

    private $productService;
    private $mediaService;
    private $productEventService;

    public function __construct(
        ProductService $productService,
        MediaService $mediaService,
        ProductEventService $productEventService
    ) {
        $this->productService = $productService;
        $this->mediaService = $mediaService;
        $this->productEventService = $productEventService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::ProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only('term')),
            'products' => $this->productService->getRecords(
                $request->term
            ),
            'can' => [
                'edit' => $user->can('product.edit'),
                'add' => $user->can('product.add'),
                'delete' => $user->can('product.delete'),
            ],
        ]));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return Inertia::render('Ecommerce::ProductCreate', $this->getData([
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
        ]));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(ProductCreateRequest $request)
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
            'roles' => empty($inputs['roles']) ? [] : [$inputs['roles']],
            'duration' => 30,
            'duration_unit' => 'minute',
            'bookable_data_range_type' => 'calendar_days_into_the_future',
            'bookable_data_range' => 60,
        ];

        $product->setMeta($meta);
        $product->save();

        Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->create();

        $files = $inputs['gallery']['files'] ?? [];

        $media = [];

        if (! empty($files)) {
            foreach ($files as $file) {
                $media[] = $this->productService->upload(
                    $product,
                    $file,
                    ModuleService::MEDIA_TYPE_PRODUCT
                );
            }
        }

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('ecommerce::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Product $product)
    {
        return Inertia::render('Ecommerce::ProductEdit', $this->getData([
            'title' => $this->getEditTitle(),
            'imageMimes' => config('constants.extensions.image'),
            'roleOptions' => $this->productService->roleOptions(),
            'statusOptions' => $this->productService->statusOptions(),
            'product' => $this->productService->formObject($product),
            'eventDurationOptions' => $this->productEventService->durationOptions(),
            'event' => $this->productEventService->formResource($product),
            'timezoneOptions' => $this->productEventService->timezoneOptions(),
            'weekdays' => $this->productEventService->weekdays()->pluck('value', 'id'),
            'weeklyHours' => $this->productEventService->weeklyHours($product),
            'dateOverrides' => $this->productEventService->dateOverrides($product),
        ]));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $inputs = $request->all();

        $product->attribute_data = [
            'name' => new TranslatedText(collect([
                'en' => new Text($inputs['name']),
            ])),
            'description' => new TranslatedText(collect([
                'en' => new Text($inputs['description']),
            ])),
        ];

        $product->status = $inputs['status'];

        $product->setMeta([
            'roles' => empty($inputs['roles']) ? [] : [(int) $inputs['roles']],
        ]);

        $product->save();

        $files = $inputs['gallery']['files'] ?? [];

        $media = [];

        if (! empty($files)) {
            foreach ($files as $file) {
                $media[] = $this->productService->upload(
                    $product,
                    $file,
                    ModuleService::MEDIA_TYPE_PRODUCT
                );
            }
        }

        $mediaIds = $inputs['gallery']['deleted_media'] ?? [];

        $deletedMedia = Media::whereIn('id', $mediaIds)->get();

        foreach ($deletedMedia as $medium) {
            $this->mediaService->destroy($medium, app(MediaStorage::class));
        }

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return redirect()->route($this->baseRouteName.'.edit', $product->id);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        $this->generateFlashMessage($this->title.' deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }
}
