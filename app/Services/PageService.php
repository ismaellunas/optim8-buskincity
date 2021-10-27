<?php

namespace App\Services;

class PageService
{
    public static function transformComponentToText($data): string
    {
        $string = "";
        foreach($data['entities'] as $entity)
        {
            $class = '\\App\\Entities\\Components\\' . $entity['componentName'];
            if(class_exists($class)){
                $class = new $class($entity);
                $string .= $class->getText() . ' ';
            }
            continue;
        }

        return trim($string);
    }
}