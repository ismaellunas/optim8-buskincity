<?php

namespace App\Entities\PageBuilderComponents;

use App\Entities\StyleBlock;

abstract class DimensionComponent
{
    protected $data;
    protected $selector;

    public function __construct($data)
    {
        $this->data = $data;
        $this->selector = $this->getSelector();
    }

    protected function getSelector(): string
    {
        return '.pb-'.$this->data['id'];
    }

    protected function getDimensionStyles(
        array $styleConfig,
        StyleBlock $styleBlock
    ): StyleBlock {
        if (!empty($styleConfig['style.margin'])) {

            $marginsConfig = $styleConfig['style.margin'];

            $unit = $marginsConfig['unit'] ?? 'px';

            collect($marginsConfig)
                ->except(['unit'])
                ->filter(fn ($value) => ($value == "0" || !empty($value)))
                ->each(function ($value, $key) use ($styleBlock, $unit) {
                    $styleBlock->addStyle('margin-'.$key, $value.$unit);
                });
        }

        if (!empty($styleConfig['style.padding'])) {

            $paddingConfig = $styleConfig['style.padding'];

            $unit = $paddingConfig['unit'] ?? 'px';

            collect($paddingConfig)
                ->except(['unit'])
                ->filter(fn($value) => ($value == "0" || !empty($value)))
                ->each(function ($value, $key) use ($styleBlock, $unit) {
                    $styleBlock->addStyle('padding-'.$key, $value.$unit);
                });
        }

        return $styleBlock;
    }
}