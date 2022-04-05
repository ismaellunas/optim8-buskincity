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
    use PasswordValidationRules;

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

        $this->setLanguage($input);
        $this->setCountry($input);

        return User::factory()
            ->unverified()
            ->create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'country_code' => $input['country_code'],
                'language_id' => $input['language_id'],
            ]);
    }

    private function setLanguage(&$input): void
    {
        $referenceLanguage = app(LanguageService::class)->getReferenceLanguage();

        $input['language_id'] = $referenceLanguage ? $referenceLanguage->id : null;
    }

    private function setCountry(&$input): void
    {
        $clientData = app(IPService::class)->getClientData();

        $input['country_code'] = $clientData['location']['country']['code'];
    }
}
