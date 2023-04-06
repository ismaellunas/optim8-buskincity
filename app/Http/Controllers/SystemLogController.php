<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $userId = (int) Str::after($request->tag, 'Auth:');

        $selectedUser = null;
        if (!empty($userId)) {
            $selectedUser = $this->getManagers(null, [$userId])->first();
        }

        return Inertia::render('SystemLog/Index', [
            'title' => __('System Log'),
            'pageQueryParams' => $request->only(['tag', 'family_hash']),
            'tagAuth' => $selectedUser,
            'can' => [
                'user'=> [
                    'edit' => $user->can('user.edit'),
                ]
            ],
            'i18n' => $this->translations(),
        ]);
    }

    public function searchUsers(Request $request)
    {
        return $this->getManagers($request->term);
    }

    private function getManagers(
        ?string $term = null,
        ?array $userIds = null,
        int $limit = 15
    ): Collection {
        return User::
            backend()
            ->orWhere(function ($query) {
                $query->inRoleNames([config('permission.super_admin_role')]);
            })
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->when($userIds, function ($query, $userIds) {
                $query->whereIn('id',$userIds);
            })
            ->limit($limit)
            ->get([
                'id',
                'first_name',
                'last_name',
            ])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'value' => $user->fullName,
                ];
            });
    }

    private function translations(): array
    {
        return [
            'select_user' => __('Select User'),
            'none' => __('None'),
            'verb' => __('Verb'),
            'path' => __('Path'),
            'status' => __('Status'),
            'user' => __('User'),
            'happened_at' => __('Happened At'),
            'actions' => __('Actions'),
            'load_new_entries' => __('Load New Entries'),
            'loading' => __('Loading'),
            'load_more' => __('Load More'),
            'request' => __('Request'),
            'request_details' => __('Request Details'),
            'time' => __('Time'),
            'hostname' => __('Hostname'),
            'method' => __('Method'),
            'controller_action' => __('Controller Action'),
            'middleware' => __('Middleware'),
            'duration' => __('Duration'),
            'status' => __('Status'),
            'ip_address' => __('Ip Address'),
            'memory' => __('Memory'),
            'authenticated_user' => __('Authenticated User'),
            'id' => __('ID'),
            'email_address' => __('Email Address'),
            'name' => __('Name'),
            'cancel' => __('Cancel'),
        ];
    }
}
