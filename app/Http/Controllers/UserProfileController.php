<?php

namespace App\Http\Controllers;

use App\Services\{
    CountryService,
    LanguageService,
};
use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController as JetUserProfileController;

class UserProfileController extends JetUserProfileController
{
    public function show(Request $request)
    {
        return Jetstream::inertia()->render($request, 'Profile/Show', [
            'sessions' => $this->sessions($request)->all(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'shownLanguageOptions' => app(LanguageService::class)->getShownLanguageOptions(),
        ]);
    }
}
