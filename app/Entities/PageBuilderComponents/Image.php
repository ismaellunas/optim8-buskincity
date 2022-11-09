<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderDimensionInterface;
use App\Traits\PageBuilderDimension;

class Image extends BaseComponent implements PageBuilderDimensionInterface
{
    use PageBuilderDimension;

    protected function composeStyleBlocks(): void
    {
        if ($this->doesConfigHaveDimension()) {
            $this->styleBlocks[] = $this->getDimensionStyleBlock(
                $this->getSelector()
            );
        }
    }

    protected function composeMobileStyleBlocks(): void
    {
        if ($this->doesConfigHaveDimension()) {
            $this->mobileStyleBlocks[] = $this->getDimensionStyleBlock(
                $this->getSelector(),
                true
            );
        }
    }
}