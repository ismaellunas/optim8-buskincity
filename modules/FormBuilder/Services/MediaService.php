<?php

namespace Modules\FormBuilder\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Services\MediaService as AppMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\Media;

class MediaService extends AppMediaService
{
    public function uploadField(
        UploadedFile $file,
        MediaStorage $mediaStorage,
    ): Media {
        $media = new Media();

        $extension = null;

        $clientExtension = $file->getClientOriginalExtension();

        $fileName = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            Str::lower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        );

        if ($this->isOriginalExtensionNeeded($file)) {
            $extension = $clientExtension;
        }

        $folder = $this->getFolderPrefix().'form_builder_assets';

        $fileName = $this->getUniqueFileName(
            $fileName,
            [],
            $extension,
            $folder
        );

        $this->fillMediaWithMediaAsset(
            $media,
            $mediaStorage->upload($file, $fileName, $extension, $folder)
        );

        $media->type = Media::TYPE_FORM_BUILDER;
        $media->save();

        return $media;
    }
}
