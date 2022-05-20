<?php

namespace App\Http\Controllers;

use App\Services\{
    CountryService,
    LanguageService,
    LoginService,
};
use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController as JetUserProfileController;

class UserProfileController extends JetUserProfileController
{
    public function show(Request $request)
    {
        $data = [];
        $pageComponent = 'Profile/ShowFrontend';

        if ($request->routeIs('admin.*')) {
            $pageComponent = 'Profile/ShowAdmin';
        }

        $socialiteDrivers = [];
        if (LoginService::isConnectedAccountEnabled()) {
            $socialiteDrivers = LoginService::getAvailableSocialiteDrivers();
        }

        $canPublicPage = auth()->user()->roles->contains(function ($role) {
            return $role->hasPermissionTo('public_page.profile');
        });

        return Jetstream::inertia()->render(
            $request,
            $pageComponent,
            array_merge_recursive(
                $data,
                [
                    'can' => [
                        'public_page' => $canPublicPage,
                        'set_password' => auth()->user()->can('setPassword'),
                    ],
                    'profilePageUrl' => $canPublicPage ? auth()->user()->profile_page_url : null,
                    'sessions' => $this->sessions($request)->all(),
                    'socialiteDrivers' => $socialiteDrivers,
                    'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
                    'description' => "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.",
                ]
            )
        );
    }
}
