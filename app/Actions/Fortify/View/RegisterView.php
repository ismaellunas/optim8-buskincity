<?php

namespace App\Actions\Fortify\View;

use App\Services\IPService;
use Inertia\Inertia;

class RegisterView
{
    public function __invoke()
    {
        $userData = app(IPService::class)->getUserData();

        return Inertia::render('Auth/Register', [
            'userLocation' => $userData->location,
        ]);
    }
}
