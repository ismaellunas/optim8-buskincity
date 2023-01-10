<?php

namespace App\Http\Controllers;

use App\Services\WidgetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('DashboardAdmin', [
            "title" => __('Dashboard'),
            "widgets" => app(WidgetService::class)->generateWidgets(),
            "moduleWidgets" => app(WidgetService::class)->generateModuleWidgets($request),
        ]);
    }
}
