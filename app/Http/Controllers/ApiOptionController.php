<?php

namespace App\Http\Controllers;

use App\Services\CountryService;

class ApiOptionController extends Controller
{
    public function __construct(private CountryService $countryService) {}

    public function phoneCountryOptions()
    {
        return $this->countryService->getPhoneCountryOptions();
    }

    public function countryOptions()
    {
        return $this->countryService->getCountryOptions();
    }

    public function timezoneOptions()
    {
        return $this->countryService->getTimezoneOptions();
    }
}
