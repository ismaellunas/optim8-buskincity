<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderDimensionInterface;
use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class Card extends BaseComponent implements
    PageBuilderDimensionInterface,
    PageBuilderSearchableTextInterface
{
    use PageBuilderDimension;

    public function getText(): string
    {
        return HtmlToText::convert($this->data['content']['cardContent']['content']['html']);
    }

    protected function composeStyleBlocks(): void
    {
        if ($this->doesConfigHaveDimension()) {
            $this->styleBlocks[] = $this->getDimensionStyleBlock(
                $this->getSelector()
            );
        }
    }
}