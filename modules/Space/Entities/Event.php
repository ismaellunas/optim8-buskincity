<?php

namespace Modules\Space\Entities;

use Modules\Event\Entities\Event as EventEntity;
use Modules\Event\Entities\EventTranslation;

class Event extends EventEntity
{
    protected $translationModel = EventTranslation::class;

    public function scopeHasSpace($query, int $spaceId)
    {
        $query->whereHasMorph(
            'eventable',
            Space::class,
            function ($query) use ($spaceId) {
                $query->where('id', $spaceId);
            }
        );
    }
}
