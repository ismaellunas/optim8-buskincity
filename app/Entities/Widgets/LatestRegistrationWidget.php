<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\User;
use App\Services\UserService;

class LatestRegistrationWidget implements WidgetInterface
{
    private $baseRouteName = "admin.users";
    private $componentName = "LatestRegistration";
    private $data = [];
    private $recordLimit = 4;
    private $title = "Latest Registrations";
    private $user;
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();

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
            'permissions' => $this->getPermissions(),
            'roleOptions' => $this->userService->getRoleOptions(),
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('user.browse');
    }

    private function getPermissions(): array
    {
        return [
            'add' => $this->user->can('user.add'),
            'edit' => $this->user->can('user.edit'),
        ];
    }
}