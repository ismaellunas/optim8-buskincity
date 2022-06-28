<?php

namespace App\Entities\PageBuilderComponents;

abstract class BaseComponent
{
    protected $data;
    protected $selector;

    public function __construct($data)
    {
        $this->data = $data;
        $this->selector = $this->getSelector();
    }

    protected function getSelector(): string
    {
        return '.'.$this->data['id'];
    }
}