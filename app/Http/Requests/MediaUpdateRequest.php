<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumericDash;
use App\Services\MediaService;

class MediaUpdateRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge(
            MediaService::getTranslationRules(),
            [
                'file_name' => [
                    'required',
                    new AlphaNumericDash(),
                    'max:250'
                ],
            ]
        );
    }
}
