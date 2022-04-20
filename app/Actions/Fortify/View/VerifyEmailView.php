<?php

namespace App\Actions\Fortify\View;

use Illuminate\Http\Request;
use Inertia\Inertia;

class VerifyEmailView
{
    public function __invoke(Request $request)
    {
        $pageComponent = ($request->routeIs('admin.*')
            ? 'Auth/VerifyEmailAdmin'
            : 'Auth/VerifyEmailFrontend'
        );

        return Inertia::render($pageComponent, ['status' => session('status')]);
    }
}
