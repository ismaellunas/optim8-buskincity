<?php

namespace Modules\Booking\Entities\PageBuilderComponents;

use App\Entities\PageBuilderComponents\BaseComponent;

class EventsCalendar extends BaseComponent
{
    protected function calculateDimensionValue(
        string $key,
        mixed $value = null,
        string $spaceType = null
    ) {
        if (
            in_array($spaceType, ['margin', 'padding'])
            && in_array($key, ['right', 'left'])
        ) {
            return null;
        }

        return parent::calculateDimensionValue($key, $value, $spaceType);
    }
}
