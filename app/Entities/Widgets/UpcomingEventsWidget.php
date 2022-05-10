<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class UpcomingEventsWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Upcoming Events";
    protected $componentName = 'UpcomingEvents';

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
            'columns' => '6',
        ];
    }

    public function canBeAccessed(): bool
    {
        return true;
    }
}
