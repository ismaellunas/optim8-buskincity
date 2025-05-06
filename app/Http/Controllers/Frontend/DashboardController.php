<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $firstName = auth()->user()->first_name;

        $widgetFrontendClass = $this->getWidgetFrontendClass();
        $widgetFrontendClass = new $widgetFrontendClass();

        return Inertia::render('Dashboard', [
            "title" => "Welcome, ".$firstName,
            "widgets" => $widgetFrontendClass->generateWidgets(),
            "moduleWidgets" => $widgetFrontendClass->generateModuleWidgets($request),
            "description" => null
        ]);
    }

    public function getWidgetFrontendClass(): string
    {
        $appId = config('app.id');

        $className = $this->baseWidgetFrontendNamespace($appId);

        if (class_exists($className)) {
            return $className;
        }

        return $this->baseWidgetFrontendNamespace();
    }

    private function baseWidgetFrontendNamespace(string $appId = null): string
    {
        return "\\App\\Services\\WidgetFrontend" . ($appId ? Str::studly($appId) : '') . "Service";
    }
}
