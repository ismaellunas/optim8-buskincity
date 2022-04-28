<?php

namespace App\Http\Controllers;

use App\Services\{
    CountryService,
    LanguageService,
    LoginService,
    SettingService,
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

        $qrCodeIsDisplayed = app(SettingService::class)->qrCodePublicPageIsDisplayed();
        $canPublicPage = auth()->user()->roles->contains(function ($role) {
            return $role->hasPermissionTo('public_page.profile');
        });

        if ($canPublicPage && $qrCodeIsDisplayed) {
            $data = [
                'qrCode' => [
                    'logoUrl' => app(SettingService::class)->qrCodePublicPageLogo(),
                    'name' => auth()->user()->qr_code_logo_name,
                ]
            ];
        }

        return Jetstream::inertia()->render(
            $request,
            $pageComponent,
            array_merge_recursive(
                $data,
                [
                    'can' => [
                        'public_page' => $canPublicPage,
                    ],
                    'countryOptions' => app(CountryService::class)->getCountryOptions(),
                    'profilePageUrl' => $canPublicPage ? auth()->user()->profile_page_url : null,
                    'qrCode' => [
                        'isDisplayed' => $qrCodeIsDisplayed
                    ],
                    'sessions' => $this->sessions($request)->all(),
                    'socialiteDrivers' => $socialiteDrivers,
                    'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
                ]
            )
        );
    }
}
