<?php

namespace App\Traits;

trait FlashNotifiable
{
    protected function generateFlashMessage($message, $replace = []): void
    {
        session()->flash('message', __($message, $replace));
    }
}
