<?php

namespace App\Http\Controllers\Frontend;

use App\Entities\ProfileQrCode;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SettingService;

class QrCodeController extends Controller
{
    public function __construct(private SettingService $settingService)
    {}

    public function print(User $user)
    {
        $user->load(['metas' => function ($query) {
            $query->whereIn('key', ['stage_name']);
        }]);

        return view('prints.qrcode', [
            'logoUrl' => $this->settingService->qrCodePublicPageLogo(),
            'qrCodeOptions' => (new ProfileQrCode($user))->options(),
        ]);
    }
}
