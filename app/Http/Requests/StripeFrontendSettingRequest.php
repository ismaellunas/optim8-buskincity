<?php

namespace App\Http\Requests;

use App\Entities\UserMetaStripe;
use Illuminate\Foundation\Http\FormRequest;

class StripeFrontendSettingRequest extends FormRequest
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
