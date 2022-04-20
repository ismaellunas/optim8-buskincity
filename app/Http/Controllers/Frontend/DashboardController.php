<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\WidgetFrontendService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            "widgets" => app(WidgetFrontendService::class)->generateWidgets(),
        ]);
    }
}
