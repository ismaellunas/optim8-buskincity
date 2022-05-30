<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\{
    FormService,
    SettingService,
    TranslationService,
    UserProfileService,
};
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    private $formService;
    private $settingService;
    private $userProfileService;

    public function __construct(
        FormService $formService,
        SettingService $settingService,
        UserProfileService $userProfileService,
    ) {
        $this->formService = $formService;
        $this->settingService = $settingService;
        $this->userProfileService = $userProfileService;
    }

    public function show(User $user)
    {
        $role = $user->roleName;
        $qrCodeIsDisplayed = $this->settingService->qrCodePublicPageIsDisplayed();

        $viewName = 'profile-'.Str::kebab($role);

        $data = [
            'fieldGroups' => $this->formService->getFieldGroupValues($user),
            'locale' => TranslationService::currentLanguage(),
            'user' => $user,
            'qrCode' => [
                'isDisplayed' => $qrCodeIsDisplayed
            ],
        ];

        if ($qrCodeIsDisplayed) {
            $data = array_merge_recursive(
                $data,
                [
                    'qrCode' => [
                        'logoUrl' => $this->settingService->qrCodePublicPageLogo(),
                        'name' => $user->qr_code_logo_name,
                    ]
                ]
            );
        }

        $this->userData($data, $user);

        if (view()->exists($viewName)) {
            return view($viewName, $data);
        }

        return view('profile', $data);
    }

    private function userData(&$data, User $user): void
    {
        $topBackgroundPicture = $this->userProfileService
            ->getMedias('top_background_picture')
            ->first();

        $data = array_merge(
            $data,
            [
                'profileBackgroundUrl' => $topBackgroundPicture->file_url ?? null,
                'profilePhotoUrl' => $user->profilePhotoUrl ?? url('/images/profile-picture-default.png'),
            ]
        );
    }
}
