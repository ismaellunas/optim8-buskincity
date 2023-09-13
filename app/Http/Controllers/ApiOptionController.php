<?php

namespace App\Http\Controllers;

use App\Services\CountryService;

class ApiOptionController extends Controller
{
    public function phoneCountryOptions()
    {
        return app(CountryService::class)->getPhoneCountryOptions();
    }

    public function countryOptions()
    {
        return app(CountryService::class)->getCountryOptions();
    }
}
