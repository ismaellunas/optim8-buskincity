<?php

namespace App\Actions\Fortify\View;

use App\Services\IPService;
use Inertia\Inertia;

class RegisterView
{
    public function __invoke()
    {
        $clientData = app(IPService::class)->getClientData();

        return Inertia::render('Auth/Register', [
            'userLocation' => $clientData->location,
        ]);
    }
}
