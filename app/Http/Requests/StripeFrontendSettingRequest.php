<?php

namespace App\Http\Requests;

use App\Entities\UserMetaStripe;

class StripeFrontendSettingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return (new UserMetaStripe($this->user()))->hasAccount();
    }

    public function rules(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
