<?php

namespace App\Services;

use App\Models\PageTranslation;

class PageTranslationService
{
    protected $pageTranslation;

    public function __construct(PageTranslation $pageTranslation)
    {
        $this->pageTranslation = $pageTranslation;
    }

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

    public function getPageRecords(
        string $term,
        int $recordsPerPage = 10
    ) {

        $records = $this->pageTranslation
            ->orderBy('id', 'DESC')
            ->when($term, function($query, $term){
                $query->search($term);
            })
            ->paginate($recordsPerPage);
        return $records;
    }


}