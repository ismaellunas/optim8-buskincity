<?php

namespace App\Http\Controllers;

use App\Services\WidgetService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private $widgetService;

    public function __construct(WidgetService $widgetService)
    {
        $this->widgetService = $widgetService;
    }

    public function index()
    {
        return Inertia::render(
            $this->getComponentName(),
            [
                "widgets" => $this->widgetService->generateWidgets(),
            ]
        );
    }

    private function getComponentName(): string
    {
        return auth()->user()->can('system.dashboard')
            ? "AdminDashboard"
            : "Dashboard";
    }
}
