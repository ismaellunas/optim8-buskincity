<?php

namespace App\Http\Controllers;

use App\Services\LoginService;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
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

        return $response;
    }
}
