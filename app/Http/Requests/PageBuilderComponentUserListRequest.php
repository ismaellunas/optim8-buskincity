<?php

namespace App\Http\Requests;

use App\Services\PageBuilderService;
use App\Services\CountryService;
use Illuminate\Validation\Rule;

class PageBuilderComponentUserListRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $countryService = app(CountryService::class);
        $pageBuilderService = app(PageBuilderService::class);

        return [
            'order_by' => [
                'nullable',
                Rule::in($pageBuilderService->userListOrderOptions()->pluck('id')),
            ],
            'countries' => [
                'sometimes',
                'array',
            ],
            'countries.*' => [
                'string',
                Rule::in($countryService->getUserCountryOptions()->pluck('id')),
            ],
        ];
    }
}
