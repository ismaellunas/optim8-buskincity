<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class DefaultWidget extends BaseWidget implements WidgetInterface
{
    protected $component = 'DefaultWidget';
}
