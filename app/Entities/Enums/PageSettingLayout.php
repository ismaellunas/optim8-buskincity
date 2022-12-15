<?php

namespace App\Entities\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum PageSettingLayout: string
{
    case NO_HEADER_AND_FOOTER = 'no_header_and_footer';

    public static function options(): Collection
    {
        return collect(self::cases())
            ->map(fn ($option) => [
                'id' => $option->value, 'value' => Str::of($option->value)->title()->replace('_', ' ')
            ]);
    }
}
