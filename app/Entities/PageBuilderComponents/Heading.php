<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;

class Heading extends BaseComponent implements PageBuilderSearchableTextInterface
{
    public function getText(): string
    {
        return HtmlToText::convert($this->data['content']['heading']['html']);
    }
}