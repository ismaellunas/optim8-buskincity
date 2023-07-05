<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as DatabaseTokenRepositoryBase;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Carbon;

class DatabaseTokenRepository extends DatabaseTokenRepositoryBase
{
    /**
     * @override
     */
    public function create(CanResetPasswordContract $user, Carbon $expiredAt = null): string
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExisting($user);

        $token = $this->createNewToken();

        $payload = $this->getPayload($email, $token);

        if (! is_null($expiredAt)) {
            $payload['expired_at'] = $expiredAt;
        }

        $this->getTable()->insert($payload);

        return $token;
    }

    /**
     * @override
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()
            ->whereNull('expired_at')
            ->where('created_at', '<', $expiredAt)
            ->delete();

        $this->getTable()
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', Carbon::now())
            ->delete();
    }
}
