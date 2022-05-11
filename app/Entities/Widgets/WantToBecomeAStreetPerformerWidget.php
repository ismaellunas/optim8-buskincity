<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class WantToBecomeAStreetPerformerWidget implements WidgetInterface
{
    protected $componentName = 'WantToBecomeAStreetPerformer';
    protected $data = [];
    protected $title = "Want to Become a Street Performer?";
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
            'columns' => '12',
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->roles->isEmpty();
    }
}
