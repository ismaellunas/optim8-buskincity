<?php

namespace App\Contracts;

interface MenuBuilderInterface {
    public function getKey(): string;

    public function getTypeOptions(): array;

    public function getMenuOptions(): array;

    public function isOptionDisplayed(): bool;
}