<?php

namespace App\Http\Requests;

use App\Services\CountryService;
use App\Services\GlobalOptionService;
use App\Services\PageBuilderService;
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
        $globalOptionService = app(GlobalOptionService::class);
        $pageBuilderService = app(PageBuilderService::class);

        return [
            'order_by' => [
                'nullable',
                Rule::in($pageBuilderService->userListOrderOptions()->pluck('id')),
            ],
            'country' => [
                'nullable',
                Rule::in($countryService->getUserCountryOptions()->pluck('id')),
            ],
            'type' => [
                'nullable',
                Rule::in($globalOptionService->getUserDisciplineOptions()->pluck('id')),
            ],
            'term' => [
                'nullable',
                'max:1024',
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
