<?php

namespace App\Traits;

use App\Entities\StyleBlock;

trait PageBuilderDimension
{
    public function getDimensionStyleBlock(
        array $styleConfig,
        string $rootSelector
    ): StyleBlock {
        $styleBlock = new StyleBlock($rootSelector);

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
