<?php

namespace App\Http\Controllers;

use App\Services\WidgetService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('DashboardAdmin', [
            "widgets" => app(WidgetService::class)->generateWidgets(),
        ]);
    }
}
