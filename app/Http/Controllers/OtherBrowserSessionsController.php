<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController as JetOtherBrowserSessionsController;

class OtherBrowserSessionsController extends JetOtherBrowserSessionsController
{
    public function destroy(Request $request, StatefulGuard $guard)
    {
        $response = parent::destroy($request, $guard);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $response;
    }
}
