<?php

namespace App\Entities\PageBuilderComponents;

use App\Entities\StyleBlock;
use App\Models\Media;

class Columns extends BaseComponent
{
    protected function composeStyleBlocks(): void
    {
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

    protected function composeMobileStyleBlocks(): void
    {
        if ($this->doesConfigHaveDimension()) {
            $this->mobileStyleBlocks[] = $this->getMobileDimensionStyleBlock(
                $this->getSelector()
            );
        }

        if ($this->doesWrapperHaveBackgroundImage()) {
            $this->mobileStyleBlocks[] = $this->getBackgroundStyleBlock(
                $this->getSelector().'-background'
            );
        }
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

    protected function calculateDimensionValue(
        string $key,
        mixed $value = null,
        string $spaceType = null
    ) {
        if ($spaceType == 'margin' && in_array($key, ['right', 'left'])) {
            return 0;
        }

        if (is_null($value)) {
            return null;
        } else {
            if ($spaceType == 'margin' && $key == 'top') {
                $value = (int) $value;

                if ($value >= 0) {
                    return ($value >= $this->defaultDimensionValue)
                        ? ($this->defaultDimensionValue * 2)
                        : ($this->defaultDimensionValue + $value);
                }

                return null;
            }
        }

        return parent::calculateDimensionValue($key, $value, $spaceType);
    }
}
