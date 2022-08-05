<?php

namespace App\Entities\Menus;

class SegmentMenuBuilder extends UrlMenuBuilder
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