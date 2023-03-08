<?php

namespace App\Http\Controllers;

use App\Services\WidgetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(WidgetService $widgetService, Request $request)
    {
        return Inertia::render('DashboardAdmin', [
            "title" => __('Dashboard'),
            "widgets" => $widgetService->generateWidgets(),
            "moduleWidgets" => $widgetService->generateModuleWidgets($request),
            "storedWidgets" => $widgetService->storedWidgets(),
        ]);
    }
}
