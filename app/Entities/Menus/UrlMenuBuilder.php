<?php

namespace App\Entities\Menus;

use App\Contracts\MenuBuilderInterface;
use Illuminate\Support\Str;

class UrlMenuBuilder implements MenuBuilderInterface
{
    protected $key = 'page';

    public function getKey(): string
    {
        return Str::lower($this->getTypeOptions()['id'] ?? $this->key);
    }

    public function getTypeOptions(): array
    {
        return [
            'id' => 'url',
            'value' => 'Url',
            'model' => null,
        ];
    }

    public function getMenuOptions(): array
    {
        return [];
    }

    public function isOptionDisplayed(): bool
    {
        return true;
    }
}