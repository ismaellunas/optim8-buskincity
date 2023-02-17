<?php

namespace Modules\Booking\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Services\EventService;

class UpcomingEventRequest extends FormRequest
{
    private $routeUser;

    public function getRouteUser(): ?User
    {
        if (is_null($this->routeUser)) {
            $this->routeUser = User::where('unique_key', $this->route('userUniqueKey'))
                 ->available()
                 ->select('id', 'unique_key')
                 ->first();
        }

        return $this->routeUser;
    }

    public function rules()
    {
        $user = $this->getRouteUser();

        return [
            'city' => [
                'nullable',
                Rule::in(
                    app(EventService::class)
                        ->getUserUpcomingEventCityOptions($user->id)
                        ->pluck('id')
                ),
            ],
            'dates' => ['nullable', 'array'],
            'dates.*' => [
                'nullable',
                'date_format:Y-m-d'
            ],
        ];
    }

    public function authorize()
    {
        return !is_null($this->getRouteUser());
    }
}
