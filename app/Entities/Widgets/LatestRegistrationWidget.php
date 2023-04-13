<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Services\UserService;

class LatestRegistrationWidget implements WidgetInterface
{
    private $baseRouteName = "admin.users";
    private $componentName = "LatestRegistration";
    private $data = [];
    private $title = "Latest registrations";
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
            'title' => __($this->title),
            'componentName' => $this->componentName,
            'data' => $this->data,
            'order' => 2,
            'i18n' => [
                'add_new' => __('Add new'),
                'type' => __('Type'),
                'view_detail' => __('View detail'),
                'view_all' => __('View all'),
                'registered' => __('Registered'),
                'no_data' => __('No data'),
                'search' => __('Search'),
            ]
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