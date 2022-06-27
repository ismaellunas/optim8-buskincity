<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Entities\StyleBlock;

class Columns extends DimensionComponent implements HasStyleInterface
{
    protected $data;

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
}
