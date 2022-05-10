<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\WidgetFrontendService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $firstName = auth()->user()->first_name;

        return Inertia::render('Dashboard', [
            "title" => "Welcome, ".$firstName,
            "widgets" => app(WidgetFrontendService::class)->generateWidgets(),
        ]);
    }
}
