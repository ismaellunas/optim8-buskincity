<?php

namespace App\Services;

use App\Entities\{
    CloudinaryStorage,
    MediaAsset,
};
use App\Models\Link;
use Illuminate\Support\Str;

class LinkService
{
    public function getRecords()
    {
        return Link::query()
            ->get([
                'id',
                'image_url',
                'url',
            ])
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'file' => null,
                    'image_url' => $link->image_url,
                    'url' => $link->url,
                ];
            })
            ->all();
    }

    public function uploadImageToCloudStorage(
        $file,
        string $fileName,
        string $folderPrefix = null
    ): MediaAsset {
        $storage = new CloudinaryStorage();

        $folder = "links";

        if ($folderPrefix) {
            $folder = $folderPrefix.'_'.$folder;
        }

        $this->deleteImageOnCloudStorage($folder.'/'.$fileName);

        return $storage->upload(
            $file,
            $fileName,
            null,
            $folder,
            true,
        );
    }

    public function deleteImageOnCloudStorage(
        string $fileName
    ) {
        $storage = new CloudinaryStorage();

        $storage->destroy($fileName);
    }
}
