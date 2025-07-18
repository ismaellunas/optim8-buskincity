<?php

namespace App\Entities;

use App\Entities\CloudinaryAsset;
use App\Contracts\MediaStorageInterface;
use Illuminate\Http\UploadedFile;

class CloudinaryStorage implements MediaStorageInterface
{
    public function upload(
        UploadedFile $file,
        ?string $fileName = null,
        ?string $extension = null,
        ?string $folder = null,
        bool $invalidate = false
    ): CloudinaryAsset {

        $options = [
            'public_id' => $fileName,
            'resource_type' => 'auto',
            'invalidate' => $invalidate,
        ];

        if ($fileName) {
            $options['filename'] = $fileName;
        }

        if ($folder) {
            $options['folder'] = $folder;
        }

        $result = cloudinary()->upload(
            $file->getRealPath(),
            $options
        );

        return CloudinaryAsset::createAssetFromApiResponse(
            $result->getResponse(),
            $extension
        );
    }

    public function rename(
        string $fromName,
        string $toName,
        string $resourceType = null
    ): CloudinaryAsset {
        $options = [];
        if ($resourceType) {
            $options['resource_type'] = $resourceType;
        }

        $result = cloudinary()->uploadApi()->rename(
            $fromName,
            $toName,
            ["resource_type" => $resourceType]
        );

        return CloudinaryAsset::createAssetFromApiResponse($result);
    }

    public function destroy(string $fileName, string $fileType = null)
    {
        $options = [];
        if ($fileType) {
            $options['resource_type'] = $fileType;
        }
        cloudinary()->destroy($fileName, $options);
    }
}
