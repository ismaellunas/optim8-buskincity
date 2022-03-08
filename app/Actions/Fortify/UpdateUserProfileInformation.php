<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['required', 'string', 'max:128'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => [
                'nullable',
                'mimes:jpg,jpeg,png',
                'max:'.config('constants.one_megabyte') * 1,
            ],
            'language_id' => ['required', 'exists:App\Models\Language,id'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            Gate::authorize('update-profile-photo', $user);

            $user->updateProfilePhoto($input['photo']);
        } else if (
            $input['profile_photo_media_id'] == null
            && $user->profile_photo_media_id != null
        ) {
            $user->deleteProfilePhoto();
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'language_id' => $input['language_id'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
    {
        $user->forceFill([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'email_verified_at' => null,
            'language_id' => $input['language_id'],
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
