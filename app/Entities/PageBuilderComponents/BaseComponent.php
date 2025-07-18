<?php

namespace App\Entities\PageBuilderComponents;

use App\Contracts\HasStyleInterface;
use App\Contracts\PageBuilderDimensionInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Entities\StyleBlock;

abstract class BaseComponent implements
    HasStyleInterface,
    PageBuilderDimensionInterface
{
    protected $data;
    protected $selector;
    protected $styleBlocks = [];
    protected $mobileStyleBlocks = [];
    protected $defaultDimensionValue = 12;

    public function __construct($data)
    {
        $this->data = $data;
    }

    protected function getId(): string
    {
        return $this->data['id'];
    }

    protected function getSelector(): string
    {
        return '.pb-'.Str::lower($this->getId());
    }

    protected function getConfig(): array
    {
        return $this->data['config'] ?? [];
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
            $this->mobileStyleBlocks[] = $this->getMobileDimensionStyleBlock(
                $this->getSelector()
            );
        }
    }

    public function getStyleBlocks(): array
    {
        $this->composeStyleBlocks();

        return $this->styleBlocks;
    }

    public function getMobileStyleBlocks(): array
    {
        $this->composeMobileStyleBlocks();

        return $this->mobileStyleBlocks;
    }

    protected function doesConfigHaveDimension(): bool
    {
        return !empty($this->getConfig()['dimension']);
    }

    protected function getDimensionConfig(): array
    {
        return $this->getConfig()['dimension'];
    }

    public function getDimensionStyleBlock(
        string $rootSelector
    ): StyleBlock {
        $styleBlock = new StyleBlock($rootSelector);

        $this->getSpaceDimensionConfig('margin')
            ->each(function ($value, $key) use ($styleBlock) {
                $unit = $this->getUnitSpaceDimension('margin');

                $styleBlock->addStyle('margin-'.$key, $value.$unit.' !important');
            });

        $this->getSpaceDimensionConfig('padding')
            ->each(function ($value, $key) use ($styleBlock) {
                $unit = $this->getUnitSpaceDimension('padding');

                $styleBlock->addStyle('padding-'.$key, $value.$unit.' !important');
            });

        return $styleBlock;
    }

    public function getMobileDimensionStyleBlock(
        string $rootSelector
    ): StyleBlock {
        $styleBlock = new StyleBlock($rootSelector);

        $this->getSpaceDimensionConfig('margin', false)
            ->each(function ($value, $key) use ($styleBlock) {
                $value = $this->calculateDimensionValue($key, $value, 'margin');

                if (!is_null($value)) {
                    $unit = !in_array($value, ["auto", "inherit"])
                        ? $this->getUnitSpaceDimension('margin')
                        : null;

                    $styleBlock->addStyle('margin-'.$key, $value.$unit);
                }
            });

        $this->getSpaceDimensionConfig('padding')
            ->each(function ($value, $key) use ($styleBlock) {
                $value = $this->calculateDimensionValue($key, $value, 'padding');

                if (!is_null($value)) {
                    $unit = !in_array($value, ["auto", "inherit"])
                        ? $this->getUnitSpaceDimension('padding')
                        : null;

                    $styleBlock->addStyle('padding-'.$key, $value.$unit);
                }
            });

        return $styleBlock;
    }

    protected function calculateDimensionValue(
        string $key,
        mixed $value = null,
        string $spaceType = null
    ) {
        if ($value) {
            if ($key == 'top' || $key == 'bottom') {

                $value = (int)$value / 2;

                return ($value >= 12) ? $this->defaultDimensionValue : $value;

            }

        } elseif (!$value && ($key == 'top' || $key == 'bottom')) {
            return 'inherit';
        }

        if ($key == 'left' || $key == 'right') {
            return $this->defaultDimensionValue / 2;
        }

        return $this->defaultDimensionValue / 2;
    }

    private function getSpaceDimensionConfig(
        string $spaceType,
        bool $isFiltered = true,
    ): Collection {
        $styleConfig = $this->getDimensionConfig();

        if (!empty($styleConfig['style.'.$spaceType])) {

            return collect($styleConfig['style.'.$spaceType])
                ->except(['unit'])
                ->when($isFiltered, function ($collection) {
                    return $collection
                        ->filter(fn($value) => ($value == "0" || !empty($value)));
                });
        }

        return collect();
    }

    private function getUnitSpaceDimension(string $spaceType): string
    {
        $styleConfig = $this->getDimensionConfig();

        return $styleConfig['style.'.$spaceType]['unit']
                ?? 'px';
    }
}
