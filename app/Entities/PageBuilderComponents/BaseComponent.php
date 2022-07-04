<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;

abstract class BaseComponent implements HasStyleInterface
{
    protected $data;
    protected $selector;
    protected $styleBlocks = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    protected function getId(): string
    {
        return $this->data['id'];
    }

    protected function getSelector(): string
    {
        return '.pb-'.$this->getId();
    }

    protected function getConfig(): array
    {
        return $this->data['config'] ?? [];
    }

    protected function composeStyleBlocks(): void
    {}

    public function getStyleBlocks(): array
    {
        $this->composeStyleBlocks();

        return $this->styleBlocks;
    }
}