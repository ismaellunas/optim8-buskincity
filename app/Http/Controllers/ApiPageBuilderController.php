<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\CountryService;
use App\Services\CategoryService;
use App\Services\GlobalOptionService;

class ApiPageBuilderController extends Controller
{
    public function countryOptions()
    {
        return app(CountryService::class)->getCountryOptions();
    }

    public function typeOptions()
    {
        return app(GlobalOptionService::class)->getDisciplineOptions();
    }

    public function userListRoleOptions()
    {
        return Role::where('guard_name', 'web')
            ->whereHas('permissions', function ($q) {
                $q->where('name', 'public_page.profile');
            })
            ->get(['id', 'name'])
            ->asOptions('id', 'name');
    }

    public function postCategoryOptions(): array
    {
        return array_merge(
            [
                [
                    'value' => null,
                    'name' => 'All',
                ]
            ],
            app(CategoryService::class)->getCategoryOptions()->all()
        );
    }
}
