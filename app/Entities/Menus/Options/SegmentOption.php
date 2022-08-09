<?php

namespace App\Entities\Menus\Options;

class SegmentOption extends UrlOption
{
    protected $key = 'segment';

    public function getTypeOptions(): array
    {
        return [
            'id' => 'segment',
            'value' => 'Segment',
            'model' => null,
        ];
    }

    public function isOptionDisplayed(): bool
    {
        return false;
    }
}