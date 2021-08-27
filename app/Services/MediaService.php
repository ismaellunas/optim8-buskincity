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

    public static function getUniqueFileName(
        string $fileName,
        array $excludedIds = [],
        string $extension = null
    ): string {
        $searchFileName = $fileName;

        if (!empty($extension)) {
            $searchFileName .= '.'.$extension;
        }

        if (self::isFileNameExists($searchFileName, $excludedIds)) {
            $fileName .= (
                '_'.Str::lower(Str::random(6)).
                ($extension ? '.'.$extension : '')
            );
        }
        return $fileName;
    }
}
