<?php

namespace App\Entities\PageBuilderComponents;

class LatestPost extends Image
{
    protected function calculateDimensionValue(string $key, mixed $value = null)
    {
        if ($value && in_array($key, ['left', 'right'])) {
            return 'auto';
        }

        return parent::calculateDimensionValue($key, $value);
    }
}
