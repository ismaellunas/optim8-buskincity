<?php

namespace App\Contracts;

use App\Entities\StyleBlock;

interface PageBuilderDimensionInterface
{
    public function getDimensionStyleBlock(string $rootSelector): StyleBlock;
    public function getMobileDimensionStyleBlock(string $rootSelector): StyleBlock;
}