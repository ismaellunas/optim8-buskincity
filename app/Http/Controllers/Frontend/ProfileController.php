<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function show(User $user)
    {
        $role = $user->roleName;
        $qrCodeIsDisplayed = $this->settingService->qrCodePublicPageIsDisplayed();

        $viewName = 'profile-'.Str::kebab($role);

        $data = [
            'locale' => currentLocale(),
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
