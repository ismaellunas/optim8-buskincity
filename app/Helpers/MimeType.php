<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Symfony\Component\Mime\MimeTypes;

class MimeType
{
    public static function getMimeTypes(array $extensions): Collection
    {
        $mimes = new MimeTypes();

        return collect($extensions)
            ->map([$mimes, 'getMimeTypes'])
            ->flatten()
            ->unique()
            ->values();
    }
}
