<?php

namespace App\Contracts;

interface WidgetInterface
{
    public function data(): array;

    public function canBeAccessed(): bool;
}