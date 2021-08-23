<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Str;

class MediaService
{
    public static function isFileNameExists(string $fileName, $exceptedIds = []): bool
    {
        $queryBuilder = Media::where('file_name', $fileName);

        if (!empty($exceptedIds)) {
            $queryBuilder->whereIn('id', $exceptedIds);
        }

        return $queryBuilder->exists();
    }

    public static function getUniqueFileName(string $fileName, $exceptedIds = []): string
    {
        if (self::isFileNameExists($fileName, $exceptedIds)) {
            $fileName .= '_'.Str::lower(Str::random(6));
        }
        return $fileName;
    }
}
