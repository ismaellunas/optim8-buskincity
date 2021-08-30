<?php

namespace App\Http\Requests;

use App\Http\Requests\MediaStoreRequest;
use App\Rules\AlphaNumericDash;
use App\Services\MediaService;

class MediaUpdateRequest extends MediaStoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
