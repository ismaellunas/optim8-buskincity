<?php

namespace App\Entities\Widgets;

use App\Models\User;

abstract class BaseWidget
{
    protected $componentName = '';
    protected $title = '';
    protected User $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function data(): array
    {
        return [
            'title' => $this->getTitle(),
            'componentName' => $this->componentName,
            'data' => [
                ...$this->getData(),
                ...[
                    'i18n' => $this->i18n(),
                ],
            ],
        ];
    }

    protected function getTitle(): string
    {
        return __($this->title);
    }

    protected function getData(): array
    {
        return [];
    }

    protected function i18n(): array
    {
        return [];
    }

    public function canBeAccessed(): bool
    {
        return true;
    }
}