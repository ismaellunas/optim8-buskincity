<?php

namespace App\Http\Requests;

use App\Services\MediaService;
use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class MediaReplaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $mimes = MediaService::getExtensions();
        $max = SettingService::maxFileSize();
        
        return [
            'media_id' => [
                'required',
                'exists:App\Models\Media,id',
            ],
            'image' => [
                'required',
                'file',
                'max:'.$max,
                'mimes:'.implode(',', $mimes),
            ],
        ];
    }
}
