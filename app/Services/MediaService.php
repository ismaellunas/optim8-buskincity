<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Str;

class MediaService
{
    public static function getUniqueFileName(string $fileName): string
    {
        if (Media::where('file_name', $fileName)->exists()) {
            $fileName .= '_'.Str::lower(Str::random(6));
        }
        return $fileName;
    }
}
