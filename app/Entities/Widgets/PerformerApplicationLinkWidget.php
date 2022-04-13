<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\Role;

class PerformerApplicationLinkWidget implements WidgetInterface
{
    protected $baseRouteName = "performer-application-form";
    protected $componentName = "PerformerApplicationLink";
    protected $data = [];
    protected $title = "Performer Application";
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->data = $this->getWidgetData();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
        ];
    }

    private function getWidgetData(): array
    {
        return [
            'baseRouteName' => $this->baseRouteName,
        ];
    }

    public function canBeAccessed(): bool
    {
        $roles = Role::all()->pluck('name')->toArray();

        return !$this->user->hasAnyRole($roles);
    }
}