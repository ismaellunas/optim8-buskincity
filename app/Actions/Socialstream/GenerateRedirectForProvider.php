<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     *
     * @param  string  $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generate(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }
}
