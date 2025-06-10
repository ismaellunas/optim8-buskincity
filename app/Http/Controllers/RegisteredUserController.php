<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LoginService;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Notifications\NewUserRegisteredNotification;
use Laravel\Fortify\Http\Controllers\RegisteredUserController as FortifyRegisteredUserController;

class RegisteredUserController extends FortifyRegisteredUserController
{
    /**
     * @override
     */
    public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
    {
        $response = parent::store($request, $creator);

        if (auth()->check()) {
            LoginService::setHomeUrl($request);
        }

        $superAdmin = User::role('Super Administrator')->first();
        if ($superAdmin) {
            Notification::send($superAdmin, new NewUserRegisteredNotification(auth()->user()));
        }

        return $response;
    }
}
