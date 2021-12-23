<?php

namespace App\Entities\Components;

use App\Contracts\PageBuilderComponentInterface;
use App\Helpers\HtmlToText;

class Faq implements PageBuilderComponentInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

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