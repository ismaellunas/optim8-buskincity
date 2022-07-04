<?php

namespace App\Entities\PageBuilderComponents;

use App\Entities\StyleBlock;

class Icon extends Image
{
    protected function composeStyleBlocks(): void
    {
        parent::composeStyleBlocks();

        if (! empty($this->getConfig()['style'])) {
            $styleConfig = $this->getConfig()['style'];

            $selector = '.pb-icon-'.$this->data['id'];

            $styleBlock = new StyleBlock($selector);

            if (!empty($styleConfig['size'])) {
                $unit = 'px';

                if ($styleConfig['size'] != 0 && !empty($styleConfig['size'])) {
                    $styleBlock->addStyle('height', 'auto');
                    $styleBlock->addStyle('width', 'auto');
                    $styleBlock->addStyle('font-size', $styleConfig['size'].$unit);
                }
            }

            $this->styleBlocks[] = $styleBlock;
        }
    }
}
