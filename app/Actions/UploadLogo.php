<?php

namespace App\Actions;

use App\Entities\CloudinaryStorage;
use App\Models\Media;
use App\Services\MediaService;
use App\Services\SettingService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class UploadLogo
{
    public function handle(UploadedFile $file): Media
    {
        $media = app(MediaService::class)->upload(
            $file,
            Str::random(10),
            new CloudinaryStorage(),
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
        return app(SettingService::class)->getLogoMedia();
    }
}