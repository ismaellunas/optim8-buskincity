<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderDimensionInterface;
use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class Tabs extends BaseComponent implements
    HasStyleInterface,
    PageBuilderDimensionInterface,
    PageBuilderSearchableTextInterface
{
    use PageBuilderDimension;

    public function getText(): string
    {
        $text = '';

        foreach ($this->data['content']['tabs'] as $content) {
            $text .= HtmlToText::convert($content['name']) . ' ';
            $text .= HtmlToText::convert($content['html']) . ' ';
        }

        return trim($text);
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