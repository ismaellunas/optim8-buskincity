<?php

namespace App\Traits;

trait ViewBaseField
{
    public function getBaseFieldViewName(): string
    {
        return "base-field";
    }

    public function getViewName(): string
    {
        return $this->getBaseFieldViewName();
    }
}
