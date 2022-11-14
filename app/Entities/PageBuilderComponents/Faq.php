<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;

class Faq extends BaseComponent implements PageBuilderSearchableTextInterface
{
    public function getText(): string
    {
        $text = $this->data['content']['heading']['html'] . ' ';

        foreach ($this->data['content']['faqContent']['contents'] as $content) {
            $text .= HtmlToText::convert($content['question']) . ' ';
            $text .= HtmlToText::convert($content['answer']) . ' ';
        }

        return trim($text);
    }
}