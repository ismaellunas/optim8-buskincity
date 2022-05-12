<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class UpcomingEventsWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Upcoming Events";
    protected $componentName = 'UpcomingEvents';
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

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
        return $this->user->roles->isEmpty();
    }
}
