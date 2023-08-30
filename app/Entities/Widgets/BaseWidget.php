<?php

namespace App\Entities\Widgets;

use App\Models\User;
use App\Services\ModuleService;

abstract class BaseWidget
{
    protected $component = null;
    protected $componentModule = null;
    protected User $user;
    protected $storedSetting = [];

    public function __construct(array $storedSetting)
    {
        $this->user = auth()->user();
        $this->storedSetting = $storedSetting;
    }

    public function data(): array
    {
        return [
            ...[
                'title' => $this->getTitle(),
                'component' => $this->component,
                'componentModule' => $this->componentModule,
                'module' => $this->storedSetting['module'],
                'grid' => $this->storedSetting['grid'] ?? $this->defaultGrid(),
            ],
            ...[
                'data' => $this->getData(),
            ],
        ];
    }

    private function defaultGrid(): array
    {
        return [
            'desktop' => 6,
            'tablet' => 6,
            'mobile' => 12,
        ];
    }

    protected function getTitle(): string
    {
        return __(
            $this->storedSetting['title']
        );
    }

    protected function getData(): array
    {
        return [
            'i18n' => $this->i18n(),
            'setting' => $this->storedSetting['setting'] ?? [],
        ];
    }

    protected function i18n(): array
    {
        return collect($this->storedSetting['i18n'] ?? [])
            ->map(function (string $text) {
                return __($text, $this->replacementString());
            })
            ->all();
    }

    public function canBeAccessed(): bool
    {
        $isEnabled = $this->storedSetting['is_enabled'] ?? true
            && $this->checkModuleIsActivated();
        $role = $this->storedSetting['setting']['visibility']['role'] ?? [];

        if (! empty($role)) {
            return $this->user->hasRole($role)
                && $isEnabled;
        } elseif (
            isset($this->storedSetting['setting']['visibility']['role'])
            && empty($role)
        ) {
            return $this->user->roles->isEmpty()
                && $isEnabled;
        }

        return $isEnabled;
    }

    private function replacementString(): array
    {
        return [
            'appName' => config('app.name'),
            'role' => $this->user->roleName,
        ];
    }

    private function checkModuleIsActivated(): bool
    {
        $module = $this->storedSetting['module'];

        if (! $module) {
            return true;
        }

        return app(ModuleService::class)->isModuleActive($module);
    }
}