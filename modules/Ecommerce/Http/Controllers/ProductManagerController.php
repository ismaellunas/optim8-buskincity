<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\ProductService;

class ProductManagerController extends Controller
{
    use FlashNotifiable;

    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function search(Request $request)
    {
        return $this->productService->managers(
            $request->term,
            $request->excluded ?? []
        );
    }

    public function updateManagers(Request $request, Product $product)
    {
        $product->managers()->sync($request->managers);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Product Manager')
        ]);

        return back();
    }
}
