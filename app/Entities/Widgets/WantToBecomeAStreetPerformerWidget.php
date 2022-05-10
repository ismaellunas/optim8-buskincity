<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class WantToBecomeAStreetPerformerWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Want to Become a Street Performer?";
    protected $componentName = 'WantToBecomeAStreetPerformer';

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
            'columns' => '12',
        ];
    }

    public function canBeAccessed(): bool
    {
        return true;
    }
}
