<?php

namespace Modules\Space\Exceptions;

use Exception;;

class PageNotFoundException extends Exception
{
    public function render()
    {
        return redirect()->route('homepage');
    }
}