<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class StreetPerformersYouMightLikeWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Street Performers You Might Like";
    protected $componentName = 'StreetPerformersYouMightLike';
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
