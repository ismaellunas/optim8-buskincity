<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\{
    IPService,
    LanguageService
};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationWithoutConfirmationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['required', 'string', 'max:128'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

        $this->transform($input);

        $user = User::factory()
            ->unverified()
            ->create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'language_id' => $input['language_id'],
            ]);

        $user->setMeta('country', $input['country_code']);
        $user->saveMetas();

        return $user;
    }

    private function transform(&$input): void
    {
        $input['language_id'] = app(LanguageService::class)->getDefaultId();
        $input['country_code'] = app(IPService::class)->getCountryCode();
    }
}
