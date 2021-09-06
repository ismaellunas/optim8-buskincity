<?php

namespace App\Entities;

use App\Entities\MediaAsset;
use Cloudinary\Api\ApiResponse;

class CloudinaryAsset extends MediaAsset
{
    public static function createAssetFromApiResponse(
        ApiResponse $response,
        string $extension = null
    ): CloudinaryAsset {
        $asset = new CloudinaryAsset();
        $asset->extension = $extension ?? $response['format'] ?? '';
        $asset->fileName = $response['public_id'];
        $asset->fileType = $response['resource_type'];
        $asset->fileUrl = $response['secure_url'];
        $asset->size = $response['bytes'];
        $asset->version = $response['version'];
        $asset->assets = (array)$response;

        return $asset;
    }
}
