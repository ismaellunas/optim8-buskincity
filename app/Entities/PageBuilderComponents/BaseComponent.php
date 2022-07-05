<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use Illuminate\Support\Str;

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
        return '.pb-'.Str::lower($this->getId());
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