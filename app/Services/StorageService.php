<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    public static function getImageUrl(
        string $fileName,
        string $folder = 'images'
    ): string {
        $path = $folder ? $folder . '/' . $fileName : $fileName;

        return Storage::url($path);
    }
}