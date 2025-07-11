<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response;

class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     *
     * @param  string  $provider
     * @return \Laravel\Socialite\AbstractUser
     */
    public function resolve(string $provider): User
    {
        try {

            $user = Socialite::driver($provider)->user();

        } catch (InvalidStateException $th) {

            return abort(Response::HTTP_NOT_FOUND);

        } catch (\Throwable $th) {

            throw $th;
        }

        if (Socialstream::generatesMissingEmails()) {
            $user->email = $user->getEmail() ?? "{$user->id}@{$provider}".config('app.domain');
        }

        return $user;
    }
}
