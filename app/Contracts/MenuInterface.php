<?php

namespace App\Contracts;

interface MenuInterface {
    public function getUrl(): string;
    public function nullFields(): array;
}