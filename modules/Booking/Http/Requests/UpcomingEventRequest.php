<?php

namespace Modules\Booking\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpcomingEventRequest extends FormRequest
{
    public function rules()
    {
        return [
            'city' => ['nullable', 'string'],
            'dates' => ['nullable', 'array'],
            'dates.*' => [
                'nullable',
                'date_format:Y-m-d'
            ],
        ];
    }

    public function authorize()
    {
        $uniqueKey = $this->route('userUniqueKey');

        return User::available()->where('unique_key', $uniqueKey)->exists();
    }
}
