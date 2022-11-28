<?php

namespace App\Entities\PageBuilderComponents;

use App\Entities\StyleBlock;
use App\Models\Media;

class Columns extends BaseComponent
{
    protected function composeStyleBlocks(): void
    {
        $styleBlock = null;

        if ($this->doesConfigHaveDimension()) {
            $styleBlock = $this->getDimensionStyleBlock(
                $this->getSelector()
            );

            $this->transformDimensionStyleBlock($styleBlock);

            $this->styleBlocks[] = $styleBlock;
        }

        if ($this->doesWrapperHaveBackgroundImage()) {
            $this->styleBlocks[] = $this->getBackgroundStyleBlock(
                $this->getSelector().'-background'
            );
        }
    }

    protected function composeMobileStyleBlocks(): void
    {
        $mobileStyleBlock = null;

        if ($this->doesConfigHaveDimension()) {
            $mobileStyleBlock = $this->getMobileDimensionStyleBlock(
                $this->getSelector()
            );

            $this->transformDimensionStyleBlock($mobileStyleBlock);

            $this->mobileStyleBlocks[] = $mobileStyleBlock;
        }
    }

    private function transformDimensionStyleBlock(StyleBlock &$styleBlock): void
    {
        $styleBlock->styles->transform(function ($style, $key) {
            if ($key == 'margin-bottom') {
                return $style.' !important';
            }

            return $style;
        });
    }

    private function doesWrapperHaveBackgroundImage(): bool
    {
        return !!($this->getConfig()['wrapper']['backgroundImage'] ?? false);
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
        return Media::find($mediaId)->optimizedImageUrl ?? null;
    }
}
