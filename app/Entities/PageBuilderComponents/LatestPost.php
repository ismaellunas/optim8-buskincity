<?php

namespace App\Entities\PageBuilderComponents;

class LatestPost extends Image
{
    protected function calculateDimensionValue(
        string $key,
        mixed $value = null,
        string $spaceType = null
    ) {
        if ($spaceType == 'margin') {
            if (in_array($key, ['right', 'left'])) {
                return -1;
            }

            if (! is_null($value)) {
                if (in_array($key, ['top'])) {

                    $value = (int) $value;

                    return ($value >= 12)
                        ? $this->defaultDimensionValue * 2
                        : $this->defaultDimensionValue + $value;
                }

                return null;
            }
        }

        if ($spaceType == 'padding') {
            if (in_array($key, ['right', 'left'])) {
                return null;
            }
        }

        return parent::calculateDimensionValue($key, $value, $spaceType);
    }
}
