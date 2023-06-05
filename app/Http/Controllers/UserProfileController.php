<?php

namespace App\Http\Controllers;

use App\Services\{
    LanguageService,
    LoginService,
    MediaService,
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

        $socialiteDrivers = [];
        if (LoginService::isConnectedAccountEnabled()) {
            $socialiteDrivers = LoginService::getAvailableSocialiteDrivers();
        }

        $user = auth()->user();
        $canPublicPage = $user->hasPublicPage;

        return Jetstream::inertia()->render(
            $request,
            $pageComponent,
            [
                'can' => [
                    'public_page' => $canPublicPage,
                    'set_password' => $user->can('setPassword', $user),
                ],
                'profilePageUrl' => $canPublicPage ? $user->profile_page_url : null,
                'sessions' => $this->sessions($request)->all(),
                'socialiteDrivers' => $socialiteDrivers,
                'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
                'optimizedProfilePhotoUrl' => $user->optimizedProfilePhotoUrl,
                'instructions' => [
                    'profilePicture' => MediaService::profilePictureInstructions(),
                ],
            ]
        );
    }
}
