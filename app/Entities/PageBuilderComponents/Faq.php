<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderComponentInterface;
use App\Contracts\PageBuilderDimensionInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class Faq extends BaseComponent implements HasStyleInterface,PageBuilderComponentInterface,PageBuilderDimensionInterface
{
    use PageBuilderDimension;

    public function getText(): string
    {
        $text = $this->data['content']['heading']['html'] . ' ';

        foreach ($this->data['content']['faqContent']['contents'] as $content) {
            $text .= HtmlToText::convert($content['question']) . ' ';
            $text .= HtmlToText::convert($content['answer']) . ' ';
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