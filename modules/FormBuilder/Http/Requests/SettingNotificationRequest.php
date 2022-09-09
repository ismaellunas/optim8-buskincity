<?php

namespace Modules\FormBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingNotificationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:127'
            ],
            'send_to' => [
                'required',
                'json',
            ],
            'from_name' => [
                'nullable',
                'max:127',
            ],
            'from_email' => [
                'nullable',
                'max:127',
            ],
            'reply_to' => [
                'nullable',
                'max:127',
            ],
            'bcc' => [
                'nullable',
                'json',
            ],
            'subject' => [
                'required',
                'max:255',
            ],
            'message' => [
                'nullable',
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'send_to' => $this->convertStringToJson($this->send_to),
            'bcc' => $this->convertStringToJson($this->bcc),
        ]);
    }

    private function convertStringToJson($value)
    {
        $dataArray = array_filter(explode(',', str_replace(' ', '', $value)));

        return json_encode($dataArray);
    }
}
