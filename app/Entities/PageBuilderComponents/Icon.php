<?php

namespace App\Entities\PageBuilderComponents;

use App\Entities\StyleBlock;

class Icon extends image
{
    public function getStyleBlocks(): array
    {
        $styleBlocks = parent::getStyleBlocks();

        if (! empty($this->data['config']['style'])) {
            $styleConfig = $this->data['config']['style'];

            $selector = '.icon-'.$this->data['id'];

            $styleBlock = new StyleBlock($selector);

            if (!empty($styleConfig['size'])) {
                $unit = 'px';

                if ($styleConfig['size'] != 0 && !empty($styleConfig['size'])) {
                    $styleBlock->addStyle('height', 'auto');
                    $styleBlock->addStyle('width', 'auto');
                    $styleBlock->addStyle('font-size', $styleConfig['size'].$unit);
                }
            }

            $styleBlocks[] = $styleBlock;
        }

        return $styleBlocks;
    }
}
