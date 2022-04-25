<?php

namespace App\Http\Controllers;

use App\Services\{
    CountryService,
    LanguageService,
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

        $qrCodeIsDisplayed = app(SettingService::class)->qrCodePublicPageIsDisplayed();
        $canPublicPage = auth()->user()->roles->contains(function ($role) {
            return $role->hasPermissionTo('public_page.profile');
        });

        if ($canPublicPage && $qrCodeIsDisplayed) {
            $data = [
                'profilePageUrl' => auth()->user()->profile_page_url,
                'qrCode' => [
                    'logoUrl' => app(SettingService::class)->qrCodePublicPageLogo(),
                    'name' => auth()->user()->qr_code_logo_name,
                ]
            ];
        }

        return Jetstream::inertia()->render(
            $request, $pageComponent,
            array_merge_recursive(
                $data,
                [
                    'can' => [
                        'public_page' => $canPublicPage,
                    ],
                    'sessions' => $this->sessions($request)->all(),
                    'countryOptions' => app(CountryService::class)->getCountryOptions(),
                    'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
                    'qrCode' => [
                        'isDisplayed' => $qrCodeIsDisplayed
                    ],
                ]
            )
        );
    }
}
