<?php

namespace App\Actions;

use App\Entities\CloudinaryStorage;
use App\Models\Media;
use App\Services\{
    MediaService,
    SettingService,
};
use Illuminate\Support\{
    Facades\App,
    Str
};

class UploadQrCodeLogo
{
    public function handle(array $inputs, string $key): Media
    {
        $media = app(MediaService::class)->uploadSetting(
            $inputs[$key],
            Str::random(10),
            new CloudinaryStorage(),
            (!App::environment('production') ? config('app.env') : null)
        );

        $existingMedia = $this->getExistingMedia();

        if ($existingMedia) {
            app(MediaService::class)->destroy(
                $existingMedia,
                new CloudinaryStorage()
            );
        }

        return $media;
    }

    protected function getExistingMedia(): ?Media
    {
        return app(SettingService::class)->getQrCodePublicPageLogoMedia();
    }
}