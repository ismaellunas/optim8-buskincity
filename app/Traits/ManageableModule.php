<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ManageableModule
{
    public function navigations(): array
    {
        $navigations = $this->model()->navigations;

        return (
            empty($navigations)
            ? $this->defaultNavigations()
            : $navigations
        );
    }

    public function menusFromNavigations(User $user, Request $request): array
    {
        $menus = [];

        foreach ($this->navigations() as $navigation) {
            $menus[] = [
                'title' => __(Arr::get($navigation, 'title')),
                'link' => route(Arr::get($navigation, 'route')),
                'isActive' => $request->routeIs(Arr::get($navigation, 'routeIs')),
                'isEnabled' => Arr::get($this->menuPermissions($user), Arr::get($navigation, 'route'), false),
            ];
        }

        return $menus;
    }

    public function adminMenus(Request $request): array
    {
        $user = $request->user();
        $children = collect($this->menusFromNavigations($user, $request));

        return [
            'title' => Str::title(__($this->model()->title)),
            'isActive' => $children->contains('isActive', true),
            'isEnabled' => $children->contains('isEnabled', true),
            'children' => $children->all(),
        ];
    }
}
