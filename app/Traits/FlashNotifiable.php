<?php

namespace App\Traits;

trait FlashNotifiable
{
    protected function generateFlashMessage($message): void
    {
        session()->flash('message', $message);
    }
}
