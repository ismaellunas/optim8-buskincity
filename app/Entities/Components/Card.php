<?php

namespace App\Entities\Components;

use App\Contracts\PageBuilderComponentInterface;
use App\Helpers\HtmlToText;

class Card implements PageBuilderComponentInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getText(): string
    {
        return HtmlToText::convert($this->data['content']['cardContent']['content']['html']);
    }
}