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
            ->filter(fn($value) => ($value == "0" || !empty($value)))
            ->each(function ($value, $key) use ($styleBlock) {
                $unit = $this->getUnitSpaceDimension('margin');

                $styleBlock->addStyle('margin-'.$key, $value.$unit);
            });

        $this->getSpaceDimensionConfig('padding')
            ->filter(fn($value) => ($value == "0" || !empty($value)))
            ->each(function ($value, $key) use ($styleBlock) {
                $unit = $this->getUnitSpaceDimension('padding');

                $styleBlock->addStyle('padding-'.$key, $value.$unit);
            });

        return $styleBlock;
    }

    public function getMobileDimensionStyleBlock(
        string $rootSelector
    ): StyleBlock {
        $styleBlock = new StyleBlock($rootSelector);

        $this->getSpaceDimensionConfig('margin')
            ->each(function ($value, $key) use ($styleBlock) {
                $value = $this->calculateDimensionValue($key, $value);
                $unit = ($value != "auto")
                    ? $this->getUnitSpaceDimension('margin')
                    : null;

                $styleBlock->addStyle('margin-'.$key, $value.$unit);
            });

        $this->getSpaceDimensionConfig('padding')
            ->filter(fn($value) => ($value == "0" || !empty($value)))
            ->each(function ($value, $key) use ($styleBlock) {
                $value = $this->calculateDimensionValue($key, $value);
                $unit = ($value != "auto")
                    ? $this->getUnitSpaceDimension('padding')
                    : null;

                $styleBlock->addStyle('padding-'.$key, $value.$unit);
            });

        return $styleBlock;
    }

    protected function calculateDimensionValue(string $key, mixed $value = null)
    {
        if (
            $value
            && ($key == 'top' || $key == 'bottom')
        ) {
            $value = (int)$value / 2;

            return ($value >= 12) ? $value : $this->defaultDimensionValue;
        } else if (
            !$value
            && ($key == 'top' || $key == 'bottom')
        ) {
            return 'auto';
        }

        return $this->defaultDimensionValue;
    }

    private function getSpaceDimensionConfig(string $spaceType): Collection
    {
        $styleConfig = $this->getDimensionConfig();

        if (!empty($styleConfig['style.'.$spaceType])) {

            return collect($styleConfig['style.'.$spaceType])
                ->except(['unit']);
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