<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;

class UserPasswordRequest extends BaseFormRequest
{
    use PasswordValidationRules;

    protected $errorBag = 'userUpdate';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }
}
