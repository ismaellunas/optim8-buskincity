<?php

namespace Modules\Ecommerce\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Ecommerce\Services\ProductEventService;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "Product";
    protected $baseRouteName = "ecommerce.products";

    private $productService;

    public function __construct(
        ProductService $productService,
        ProductEventService $productEventService
    ) {
        $this->productService = $productService;
        $this->productEventService = $productEventService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::FrontendProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only('term')),
            'products' => $this->productService->getFrontendRecords(
                $user,
                $request->term
            ),
        ]));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($product)
    {
        return Inertia::render('Ecommerce::FrontendProductShow', $this->getData([
            'title' => $product->translateAttribute('name', config('app.locale')),
        ]));
    }
}
