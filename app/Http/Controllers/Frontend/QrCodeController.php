<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Services\SettingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function print(User $user)
    {
        return view('prints.qrcode', [
            'logoUrl' => app(SettingService::class)->qrCodePublicPageLogo(),
            'text' => $user->profile_page_url,
        ]);
    }
}
