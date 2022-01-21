<?php

namespace App\Helpers;
use MatthiasMullie\Minify;

class MinifyCss
{
    public static function minify($css)
    {
        $minifier = new Minify\CSS();
        $minifier->add($css);
        return $minifier->minify();
    }
}