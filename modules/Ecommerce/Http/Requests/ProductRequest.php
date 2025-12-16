<?php

namespace Modules\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Services\ProductSpaceService;
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
        $rules = [
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
                Rule::in(app(ProductService::class)->roleOptions()->pluck('id')->filter())
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

        // Add space_id validation if Space module is enabled
        if (\Nwidart\Modules\Facades\Module::has('Space') && \Nwidart\Modules\Facades\Module::isEnabled('Space')) {
            // Get the current product's space ID if we're updating
            $currentSpaceId = null;
            if ($this->route('product')) {
                $currentSpaceId = $this->route('product')->productable_id;
            }
            
            $spaceIds = app(ProductSpaceService::class)
                ->getSpaceOptions($currentSpaceId)
                ->filter(fn ($space) => ! $space['is_disabled'])
                ->pluck('id');

            $rules['space_id'] = [
                'nullable',
                Rule::in($spaceIds),
            ];
        }

        return $rules;
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
