<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    private $advanceUpdateProfile = false;

    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
    {
        $this->validator($user, $input);

        if (isset($input['photo'])) {
            Gate::authorize('update-profile-photo', $user);

            $user->updateProfilePhoto($input['photo']);
        } else if (
            $input['is_photo_deleted']
            && $user->profile_photo_media_id != null
        ) {
            $user->deleteProfilePhoto();
        }

        if (
            $input['email'] !== $user->email
            && $user instanceof MustVerifyEmail
            && $this->advanceUpdateProfile
        ) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $inputs = [
                'language_id' => $input['language_id'],
            ];

            $this->advanceUpdateProfileInputs($inputs, $input);

            $user->forceFill($inputs)->save();
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
        $inputs = [
            'email_verified_at' => null,
            'language_id' => $input['language_id'],
        ];

        $this->advanceUpdateProfileInputs($inputs, $input);

        $user->forceFill($inputs)->save();

        $user->sendEmailVerificationNotification();
    }

    private function validator(User $user, array $input): void
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['required', 'string', 'max:128'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'photo' => [
                'nullable',
                'mimes:jpg,jpeg,png',
                'max:'.config('constants.one_megabyte') * 1,
            ],
            'language_id' => ['required', 'exists:App\Models\Language,id'],
        ],
        [],
        [
            'language_id' => __('validation.attributes.language_id'),
        ])->validateWithBag('updateProfileInformation');
    }

    private function advanceUpdateProfileInputs(&$inputs, $input): void
    {
        if ($this->advanceUpdateProfile) {
            $inputs = array_merge($inputs, [
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
            ]);
        }
    }
}
