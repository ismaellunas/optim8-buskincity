<?php

namespace App\Actions;

use App\Models\Media;
use App\Services\SettingService;

class UploadFavicon extends UploadLogo
{
    protected function getExistingMedia(): ?Media
    {
        return app(SettingService::class)->getFaviconMedia();
    }
}