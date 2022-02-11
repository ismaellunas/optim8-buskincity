<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Fortify;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => 'OK'], 200);
        } else {
            $loginRoute = 'login';
            $routeName = $request->route()->getName();

            if ($routeName == "admin.password.update") {
                $loginRoute = 'admin.login';
            }

            return redirect()
                ->route($loginRoute)
                ->with('message', 'Reset password successfully!');
        }
    }
}
