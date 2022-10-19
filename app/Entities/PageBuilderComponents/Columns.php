<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\PageBuilderDimensionInterface;
use App\Entities\StyleBlock;
use App\Models\Media;
use App\Traits\PageBuilderDimension;

class Columns extends BaseComponent implements
    PageBuilderDimensionInterface
{
    use PageBuilderDimension;

    protected function composeStyleBlocks(): void
    {
        $styleBlock = null;

        if ($this->doesConfigHaveDimension()) {
            $this->styleBlocks[] = $this->getDimensionStyleBlock(
                $this->getSelector()
            );
        }

        if ($this->doesWrapperHaveBackgroundImage()) {
            $this->styleBlocks[] = $this->getBackgroundStyleBlock(
                $this->getSelector().'-background'
            );
        }
    }

    private function doesWrapperHaveBackgroundImage(): bool
    {
        return !!$this->getConfig()['wrapper']['backgroundImage'] ?? false;
    }

    private function getBackgroundStyleBlock(string $rootSelector): StyleBlock
    {
        $styleBlock = new StyleBlock($rootSelector);

        $backgroundUrl = $this->getMediaUrl(
            $this->getConfig()['wrapper']['backgroundImage']
        );

        if ($backgroundUrl) {
            $styleBlock->addStyle('background-image', 'url('.$backgroundUrl.')');
        }

        return $styleBlock;
    }

    private function getMediaUrl(int $mediaId): ?string
    {
        return Media::find($mediaId)->file_url ?? null;
    }
}
