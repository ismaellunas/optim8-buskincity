<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;

class Tabs extends BaseComponent implements PageBuilderSearchableTextInterface
{
    public function getText(): string
    {
        $text = '';

        foreach ($this->data['content']['tabs'] as $content) {
            $text .= HtmlToText::convert($content['name']) . ' ';
            $text .= HtmlToText::convert($content['html']) . ' ';
        }

        return trim($text);
    }
}