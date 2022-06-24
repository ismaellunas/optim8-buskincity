<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderComponentInterface;
use App\Helpers\HtmlToText;

class Heading implements PageBuilderComponentInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getText(): string
    {
        return HtmlToText::convert($this->data['content']['heading']['html']);
    }
}