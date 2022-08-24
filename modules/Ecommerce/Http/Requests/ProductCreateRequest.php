<?php

namespace Modules\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Ecommerce\Services\ProductService;

class ProductCreateRequest extends FormRequest
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
                Rule::in(['published', 'draft']),
            ],
            'roles' => [
                'nullable',
                Rule::in(app(ProductService::class)->roleOptions()->pluck('id'))
            ],
            'gallery.files.*' => [
                'max:500',
                'mimes:png,jpg,jpeg',
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
