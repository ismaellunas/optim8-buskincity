<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Entities\StyleBlock;

class Columns implements HasStyleInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getStyleBlocks(): array
    {
        $styleBlocks = [];

        if (! empty($this->data['config']['wrapper'])) {
            $wrapperConfig = $this->data['config']['wrapper'];

            $selector = '#'.$this->data['id'];

            $styleBlock = new StyleBlock($selector);

            if (!empty($wrapperConfig['style.margin'])) {

                $marginsConfig = $wrapperConfig['style.margin'];

                $unit = $marginsConfig['unit'] ?? 'px';

                collect($marginsConfig)
                    ->except(['unit'])
                    ->filter(fn ($value) => ($value == "0" || !empty($value)))
                    ->each(function ($value, $key) use ($styleBlock, $unit) {
                        $styleBlock->addStyle('margin-'.$key, $value.$unit);
                    });
            }

            if (!empty($wrapperConfig['style.padding'])) {

                $paddingConfig = $wrapperConfig['style.padding'];

                $unit = $paddingConfig['unit'] ?? 'px';

                collect($paddingConfig)
                    ->except(['unit'])
                    ->filter(fn($value) => ($value == "0" || !empty($value)))
                    ->each(function ($value, $key) use ($styleBlock, $unit) {
                        $styleBlock->addStyle('padding-'.$key, $value.$unit);
                    });
            }

            $styleBlocks[] = $styleBlock;
        }

        return $styleBlocks;
    }
}
