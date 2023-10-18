<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Support\Arr;
use Modules\Booking\Http\Requests\ProductSpaceRequest;
use Modules\Booking\Services\ProductSpaceService;
use Modules\Ecommerce\Entities\Product;

class ProductSpaceController extends CrudController
{
    protected $title = 'Product Space';

    public function __construct(private ProductSpaceService $productSpaceService)
    {}

    public function update(ProductSpaceRequest $request, Product $product)
    {
        $spaceId = Arr::get($request->validated(), 'space_id');

        if ($spaceId) {
            $product->productable_type = "Modules\Space\Entities\Space";
            $product->productable_id = $spaceId;
        } else {
            $product->productable_type = null;
            $product->productable_id = null;
        }

        $product->save();

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title
        ]);

        return back();
    }
}
