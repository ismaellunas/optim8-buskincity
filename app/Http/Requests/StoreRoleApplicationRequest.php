<?php

namespace App\Http\Requests;

use App\Services\RoleApplicationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = \App\Services\SettingService::maxFileSize();
        $allowed = config('constants.extensions.image');

        return array_merge(
            app(RoleApplicationService::class)->submissionRules(),
            [
                'logo' => ['nullable', 'file', 'mimes:'.implode(',', $allowed), 'max:'.$maxKb],
                'cover' => ['nullable', 'file', 'mimes:'.implode(',', $allowed), 'max:'.$maxKb],
            ]
        );
    }
}
