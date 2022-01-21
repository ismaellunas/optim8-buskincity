<?php

namespace App\Contracts;

interface MutatedValueFieldInterface
{
    public function mutateValue(mixed $value): mixed;
}
