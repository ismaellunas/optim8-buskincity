<?php

namespace App\Contracts;

use App\Entities\StyleBlock;

interface PageBuilderDimensionInterface
{
    public function getDimensionStyleBlock(
        array $styleConfig,
        string $rootSelector
    ): StyleBlock;
}