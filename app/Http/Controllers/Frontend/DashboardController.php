<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\WidgetFrontendService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $firstName = auth()->user()->first_name;

        return Inertia::render('Dashboard', [
            "title" => "Welcome, ".$firstName,
            "widgets" => app(WidgetFrontendService::class)->generateWidgets(),
            "moduleWidgets" => app(WidgetFrontendService::class)->generateModuleWidgets($request),
            "description" => "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua."
        ]);
    }
}
