<?php

namespace App\Helpers;

use Spatie\Color\Contrast;
use Spatie\Color\Hex;

class Color
{
    public static function getTextColorFromBackground(string $hexBackground): string
    {
        $black = '#000000';
        $white = '#ffffff';
        $hexBgColor = Hex::fromString($hexBackground);

        $ratio[$black] = Contrast::ratio(Hex::fromString($black), $hexBgColor);
        $ratio[$white] = Contrast::ratio(Hex::fromString($white), $hexBgColor);

        $color = array_keys($ratio, max($ratio));

        return $color[0] ?? $black;
    }
}
