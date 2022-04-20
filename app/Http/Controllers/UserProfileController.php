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
        $pageComponent = 'Profile/ShowFrontend';

        if ($request->routeIs('admin.*')) {
            $pageComponent = 'Profile/ShowAdmin';
        }

        return Jetstream::inertia()->render($request, $pageComponent, [
            'sessions' => $this->sessions($request)->all(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
        ]);
    }
}
