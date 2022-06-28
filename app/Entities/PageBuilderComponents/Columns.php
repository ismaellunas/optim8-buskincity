<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderDimensionInterface;
use App\Traits\PageBuilderDimension;

class Columns extends BaseComponent implements HasStyleInterface,PageBuilderDimensionInterface
{
    use PageBuilderDimension;

    public function getStyleBlocks(): array
    {
        $styleBlocks = [];

        if (! empty($this->data['config']['dimension'])) {
            $dimensionConfig = $this->data['config']['dimension'];

            $styleBlocks[] = $this->getDimensionStyleBlock(
                $dimensionConfig,
                $this->selector
            );
        }

        return $styleBlocks;
    }
}
