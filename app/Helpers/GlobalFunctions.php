<?php

use App\Helpers\HumanReadable;
use App\Services\TranslationService;


if (! function_exists('defaultLocale')) {
    function defaultLocale()
    {
        return TranslationService::getDefaultLocale();
    }
}

if (! function_exists('currentLocale')) {
    function currentLocale()
    {
        return TranslationService::currentLanguage();
    }
}

if (! function_exists('defaultMediaLibraryInstructions')) {
    function defaultMediaLibraryInstructions()
    {
        return [
            __('Accepted file extensions: :extensions.', [
                'extensions' => implode(', ', config('constants.extensions.image'))
            ]),
            __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    (50 * config('constants.one_megabyte')) * 1024
                )
            ]),
        ];
    }
}
