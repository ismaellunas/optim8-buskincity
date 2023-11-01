<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Services\ProductSpaceService;

class ProductSpaceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $spaceIds = app(ProductSpaceService::class)
            ->getSpaceOptions($this->route('product')->productable_id)
            ->filter(fn ($space) => ! $space['is_disabled'])
            ->pluck('id');

        return [
            'space_id' => [
                'nullable',
                Rule::in($spaceIds),
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
