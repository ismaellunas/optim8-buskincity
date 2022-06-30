<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderDimensionInterface;
use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class CardText extends BaseComponent implements
    HasStyleInterface,
    PageBuilderDimensionInterface,
    PageBuilderSearchableTextInterface
{
    use PageBuilderDimension;

    public function getText(): string
    {
        return HtmlToText::convert($this->data['content']['cardContent']['content']['html']);
    }

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