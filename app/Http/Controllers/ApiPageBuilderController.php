<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\CountryService;

class ApiPageBuilderController extends Controller
{
    public function countryOptions()
    {
        return app(CountryService::class)->getCountryOptions();
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
}
