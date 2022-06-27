<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Entities\StyleBlock;

class Carousel extends DimensionComponent implements HasStyleInterface
{
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