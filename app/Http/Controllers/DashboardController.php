<?php

namespace App\Http\Controllers;

use App\Services\WidgetService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render(
            $this->getComponentName(),
            [
                "widgets" => app(WidgetService::class)->generateWidgets(),
            ]
        );
    }

    private function getComponentName(): string
    {
        $componentName = 'Dashboard';

        if (
            Route::currentRouteName() == 'admin.dashboard'
            && auth()->user()->can('system.dashboard')
        ) {
            $componentName = 'AdminDashboard';
        }

        return $componentName;
    }
}
