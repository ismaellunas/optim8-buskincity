<?php

namespace App\Auth;

use Closure;
use Illuminate\Auth\Passwords\PasswordBroker as Broker;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Support\Carbon;

class PasswordBroker extends Broker implements PasswordBrokerContract
{
    public function sendResetLink(array $credentials, Closure $callback = null, Carbon $expiredAt = null): string
    {
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if ($this->tokens->recentlyCreatedToken($user)) {
            return static::RESET_THROTTLED;
        }

        $token = $this->tokens->create($user, $expiredAt);

        if ($callback) {
            $callback($user, $token);
        } else {
            $user->sendPasswordResetNotification($token);
        }

        return static::RESET_LINK_SENT;
    }
}
