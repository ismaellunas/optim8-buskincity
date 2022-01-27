<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\Caches\WidgetCache;
use App\Models\User;
use App\Services\WidgetService;

class UserWidget implements WidgetInterface
{
    protected $baseRouteName = "admin.users";
    protected $componentName = "User";
    protected $data = [];
    protected $recordLimit = 4;
    protected $title = "Manage Users";
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
            'records' => $this->getRecords(),
            'permissions' => $this->getPermissions(),
        ];
    }

    private function getRecords(): array
    {
        return app(WidgetCache::class)->remember(
            app(WidgetService::class)->getWidgetName('user'),
            function () {
                return User::latest()
                    ->select([
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'is_suspended',
                        'profile_photo_media_id',
                        'created_at',
                    ])
                    ->limit($this->recordLimit)
                    ->get()
                    ->append('registered_at')
                    ->toArray();
            }
        );
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('user.browse');
    }

    private function getPermissions(): array
    {
        return [
            'add' => $this->user->can('user.add'),
            'delete' => $this->user->can('user.delete'),
            'edit' => $this->user->can('user.edit'),
        ];
    }
}