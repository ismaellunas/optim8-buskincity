<?php

namespace App\Helpers;

class CssUnitConverter
{
    public static function pxToEm(float $px, float $defaultPx = 16): float
    {
        return round($px / $defaultPx, 4);
    }
}
