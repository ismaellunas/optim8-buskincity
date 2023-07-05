<?php

namespace App\Traits;

trait FlashNotifiable
{
    protected function generateFlashMessage($message, $replace = [], $number = 1): void
    {
        session()->flash('message', trans_choice($message, $number, $replace));
    }
}
