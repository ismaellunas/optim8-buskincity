<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderDimensionInterface;
use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class Tabs extends BaseComponent implements
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

    protected function composeStyleBlocks(): void
    {
        if ($this->doesConfigHaveDimension()) {
            $this->styleBlocks[] = $this->getDimensionStyleBlock(
                $this->getSelector()
            );
        }
    }
}