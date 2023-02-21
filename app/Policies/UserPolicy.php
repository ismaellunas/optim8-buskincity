<?php

namespace App\Policies;

use App\Models\User;
use App\Services\{
    StripeService,
    StripeSettingService,
};
use Illuminate\Database\Eloquent\Model;

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

    public function update(User $user, Model $selectedUser)
    {
        $isPermitted = parent::update($user, $selectedUser);

        if ($selectedUser->isSuperAdministrator) {
            $isPermitted = $isPermitted && $user->isSuperAdministrator;
        }

        return $isPermitted;
    }

    public function updatePassword(User $user, Model $selectedUser)
    {
        return (
            parent::update($user, $selectedUser)
            && !$selectedUser->isConnectedAccount
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

    public function manageStripeConnectedAccount(User $user)
    {
        return (
            app(StripeSettingService::class)->isEnabled()
            && app(StripeService::class)->isStripeKeyExists()
            && $user->can('payment.management')
        );
    }

    public function receiveDonation(?User $user, Model $selectedUser)
    {
        return (
            app(StripeSettingService::class)->isEnabled()
            && app(StripeService::class)->isStripeKeyExists()
            && app(StripeService::class)->isStripeConnectEnabled($selectedUser)
        );
    }

    public function setPassword(User $user, Model $selectedUser)
    {
        return (
            !$selectedUser->isConnectedAccount
            && ($selectedUser->id == $user->id || $this->update($user, $selectedUser))
        );
    }

    public function manageStripeSetting(User $user)
    {
        return app(StripeService::class)->isStripeKeyExists()
            && $user->can('system.payment');
    }

    public function manageUserTrashed(User $user)
    {
        return $user->isSuperAdministrator;
    }
}
