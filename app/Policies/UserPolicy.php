<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Services\StripeService;

class UserPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'user';

    public function delete(User $user, Model $selectedUser)
    {
        return (
            parent::delete($user, $selectedUser)
            && !$selectedUser->isSuperAdministrator
            && $user->id != $selectedUser->id
        );
    }

    public function suspend(User $user, Model $selectedUser)
    {
        return (
            $this->delete($user, $selectedUser)
            && !$selectedUser->is_suspended
        );
    }

    public function unsuspend(User $user, Model $selectedUser)
    {
        return (
            $this->delete($user, $selectedUser)
            && $selectedUser->is_suspended
        );
    }

    public function updateProfilePhoto(User $user, Model $selectedUser)
    {
        return (
            parent::update($user, $selectedUser)
            || $user->id == $selectedUser->id
        );
    }

    public function updateStripeConnect(User $user)
    {
        return (
            app(StripeService::class)->isEnabled()
            && $user->can('payment.management')
        );
    }

    public function receiveDonation(?User $user, Model $selectedUser)
    {
        $stripeService = app(StripeService::class);

        return (
            $stripeService->isEnabled()
            && $stripeService->isStripeConnectEnabled($selectedUser)
        );
    }
}
