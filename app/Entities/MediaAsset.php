<?php

namespace App\Entities;

abstract class MediaAsset
{
    public array $assets;
    public string $extension;
    public string $fileName;
    public string $fileType;
    public string $fileUrl;
    public string $size;
    public string $version;
}
