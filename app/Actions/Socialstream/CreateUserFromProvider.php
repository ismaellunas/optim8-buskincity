<?php

namespace App\Actions\Socialstream;

use App\Models\{
    Language,
    User
};
use App\Services\{
    IPService,
    UserService,
};
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUserContract;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * The creates connected accounts instance.
     *
     * @var \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts
     */
    public $createsConnectedAccounts;

    /**
     * Create a new action instance.
     *
     * @param  \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts  $createsConnectedAccounts
     */
    public function __construct(CreatesConnectedAccounts $createsConnectedAccounts)
    {
        $this->createsConnectedAccounts = $createsConnectedAccounts;
    }

    /**
     * Create a new user from a social provider user.
     *
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \App\Models\User
     */
    public function create(string $provider, ProviderUserContract $providerUser): mixed
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            $name = UserService::splitName($providerUser->getName() ?? $providerUser->getNickname());
            $languageId = Language::where('code', 'en')->value('id') ?? null;

            return tap(User::factory()->create([
                'first_name' => $name['firstName'],
                'last_name' => $name['lastName'],
                'email' => $providerUser->getEmail(),
                'language_id' => $languageId,
                'password' => null,
            ]), function (User $user) use ($provider, $providerUser) {
                $user->markEmailAsVerified();

                if (Socialstream::hasProviderAvatarsFeature() && Jetstream::managesProfilePhotos() && $providerUser->getAvatar()) {
                    $user->setProfilePhotoFromUrl($providerUser->getAvatar());
                }

                $user->switchConnectedAccount(
                    $this->createsConnectedAccounts->create($user, $provider, $providerUser)
                );

                $user->setMeta('country', app(IPService::class)->getCountryCode());
                $user->saveMetas();
            });
        });
    }
}
