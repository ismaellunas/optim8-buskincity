<?php

namespace App\Actions;

use App\Models\Media;
use App\Services\StripeSettingService;

class UploadStripeLogo extends UploadLogo
{
    protected function getExistingMedia(): ?Media
    {
        return app(StripeSettingService::class)->logoMedia();
    }
}