<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderDimensionInterface;
use App\Contracts\PageBuilderSearchableTextInterface;
use App\Helpers\HtmlToText;
use App\Traits\PageBuilderDimension;

class Faq extends BaseComponent implements
    PageBuilderDimensionInterface,
    PageBuilderSearchableTextInterface
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