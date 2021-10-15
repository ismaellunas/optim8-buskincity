<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumericDash;
use App\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
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
