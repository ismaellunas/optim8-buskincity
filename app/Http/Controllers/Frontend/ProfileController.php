<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\{
    FormService,
    SettingService,
    TranslationService
};
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    private $formService;
    private $settingService;

    public function __construct(
        FormService $formService,
        SettingService $settingService,
    ) {
        $this->formService = $formService;
        $this->settingService = $settingService;
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

        if (view()->exists($viewName)) {
            return view($viewName, $data);
        }

        return view('profile', $data);
    }
}
