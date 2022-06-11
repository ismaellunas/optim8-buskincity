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

    public function roleOptions()
    {
        return Role::where('guard_name', 'web')
            ->get(['id', 'name'])
            ->asOptions('id', 'name');
    }
}
