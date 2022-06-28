<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderComponentInterface;
use App\Contracts\PageBuilderDimensionInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class Tabs extends BaseComponent implements HasStyleInterface,PageBuilderComponentInterface,PageBuilderDimensionInterface
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