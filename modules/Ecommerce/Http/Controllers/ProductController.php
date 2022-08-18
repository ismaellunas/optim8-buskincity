<?php

namespace Modules\Ecommerce\Http\Controllers;

//use GetCandy\Models\Product;
//use Illuminate\Routing\Controller;
use App\Http\Controllers\CrudController;
use App\Services\UserService;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "Product";
    protected $baseRouteName = "admin.ecommerce.products";

    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
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
            'roleOptions' => app(UserService::class)->getRoleOptions(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
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
            'sku' => 'test'.date('YmdHis'),
        ]);

        $taxClass = TaxClass::getDefault();

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => $taxClass->id,
            'purchasable' => 'always',
            'shippable' => true,
            'stock' => 0,
            'backorder' => 0,
            'sku' => 'EVT-'.$product->id,
        ]);

        return back();
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
    public function edit($id)
    {
        return Inertia::render('Ecommerce::ProductEdit', $this->getData([
            'title' => $this->getEditTitle(),
        ]));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
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
