<?php

namespace App\Actions;

use App\Models\Media;
use App\Services\SettingService;

class UploadQrCodeLogo extends UploadLogo
{
    protected function getExistingMedia(): ?Media
    {
        return app(SettingService::class)->getQrCodePublicPageLogoMedia();
    }
}