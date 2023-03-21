<?php

namespace Modules\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Ecommerce\ModuleService as EcommerceModuleService;
use Modules\Ecommerce\Services\ProductService;

class ProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:200',
            ],
            'description' => [
                'max:1000'
            ],
            'status' => [
                'required',
                Rule::in(array_column(ProductStatus::cases(), 'value')),
            ],
            'roles' => [
                'nullable',
                Rule::in(app(ProductService::class)->roleOptions()->pluck('id'))
            ],
            'is_check_in_required' => [
                'required',
                'boolean',
            ],
            'gallery' => [
                'nullable',
                'array',
                'max:'.EcommerceModuleService::maxProductMediaNumber()
            ],
            'gallery.*' => [
                'exists:media,id',
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
