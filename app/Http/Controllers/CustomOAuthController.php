<?php

namespace App\Http\Controllers;

use JoelButcher\Socialstream\Http\Controllers\OAuthController;

class CustomOAuthController extends OAuthController
{

    protected function alreadyAuthenticated($user, $account, $provider, $providerAccount)
    {
        if ($account && $account->user_id !== $user->id) {
            return redirect()->route('user.profile.show')->dangerBanner(
                __('This :Provider sign in account is already associated with another user. Please try a different account.', ['provider' => $provider]),
            );
        }

        if (! $account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            return redirect()->route('user.profile.show')->banner(
                __('You have successfully connected :Provider to your account.', ['provider' => $provider])
            );
        }

        return redirect()->route('user.profile.show')->dangerBanner(
            __('This :Provider sign in account is already associated with your user.', ['provider' => $provider]),
        );
    }
}
