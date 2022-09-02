<?php

namespace Modules\Ecommerce\Http\Requests;

use Modules\Ecommerce\Http\Requests\ProductCreateRequest;
use Modules\Ecommerce\ModuleService;

class ProductUpdateRequest extends ProductCreateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $product = $this->route('product');

        if ($this->has('gallery.files')) {
            $maxMediaNumber = (int) (
                ModuleService::maxProductMediaNumber()
                - $product->gallery->count()
                + count($this->get('gallery.deleted_media', []))
            );

            $rules['gallery'] = [
                'max:'.$maxMediaNumber
            ];
        }

        return $rules;
    }
}
