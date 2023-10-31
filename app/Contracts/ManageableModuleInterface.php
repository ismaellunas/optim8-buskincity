<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface ManageableModuleInterface
{
    public function menuPermissions(User $user): array;

    public function defaultNavigations(): array;

    public function navigations(): array;

    public function menusFromNavigations(User $user, Request $request): array;

    public function adminMenus(Request $request): array;
}
