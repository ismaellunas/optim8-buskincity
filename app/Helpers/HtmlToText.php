<?php

namespace App\Helpers;

class HtmlToText
{
    public static function convert($html): string
    {
        return preg_replace('/\s+/', ' ', preg_replace( "/\r|\n/", " ", strip_tags(html_entity_decode(str_replace("&nbsp;", "", $html)))));
    }
}