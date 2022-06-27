<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderComponentInterface;
use App\Entities\StyleBlock;
use App\Helpers\HtmlToText;

class Button extends DimensionComponent implements HasStyleInterface,PageBuilderComponentInterface
{
    public function getText(): string
    {
        return HtmlToText::convert($this->data['content']['button']['text']);
    }

    public function getStyleBlocks(): array
    {
        $styleBlocks = [];

        if (! empty($this->data['config']['dimension'])) {
            $dimensionConfig = $this->data['config']['dimension'];

            $styleBlock = new StyleBlock($this->selector);

            $styleBlocks[] = $this->getDimensionStyles($dimensionConfig, $styleBlock);
        }

        return $styleBlocks;
    }

    protected function setSelector(): string
    {
        return '.'.$this->data['id'];
    }
}