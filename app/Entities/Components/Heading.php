<?php

namespace App\Entities\Components;

use App\Contracts\PageBuilderComponentInterface;

class Heading implements PageBuilderComponentInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getText(): string
    {
        return strip_tags(html_entity_decode($this->data['content']['heading']['html']));
    }
}