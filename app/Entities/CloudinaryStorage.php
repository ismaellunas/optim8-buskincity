<?php

namespace App\Entities;

use App\Entities\CloudinaryAsset;
use App\Contracts\MediaStorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile as File;

class CloudinaryStorage implements MediaStorageInterface
{
    public function upload(
        File $file,
        string $fileName = null,
        string $extension = null
    ): CloudinaryAsset {

        $result = cloudinary()->upload(
            $file->getRealPath(),
            [
                'public_id' => $fileName,
                'resource_type' => 'auto',
            ]
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
