<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StringManipulator
{
    public static function snakeToTitle(string $text): string
    {
        return Str::of($text)->snake()->replace('_', ' ')->title();
    }
}
