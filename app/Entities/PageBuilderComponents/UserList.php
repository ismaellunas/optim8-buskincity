<?php

namespace App\Entities\PageBuilderComponents;

class UserList extends Image
{
    protected function calculateDimensionValue(
        string $key,
        mixed $value = null,
        string $spaceType = null
    ) {
        if (in_array($key, ['left', 'right'])) {
            if ($spaceType == 'margin') {
                return -12;
            }

            if ($spaceType == 'padding') {
                return null;
            }
        }

        return parent::calculateDimensionValue($key, $value, $spaceType);
    }
}
