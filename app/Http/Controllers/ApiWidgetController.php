<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\WidgetService;
use Illuminate\Http\Request;

class ApiWidgetController extends Controller
{
    public function getLatestRegistrations(Request $request, UserService $userService)
    {
        $scopes = [
            'inRoles' => $request->roles ?? null,
        ];

        return [
            'records' => $userService->getLatestRegistrations(
                $request->term,
                $scopes
            ),
        ];
    }

    public function getStoredWidgetData(
        WidgetService $widgetService,
        string $uuid
    ) {
        $widget = $widgetService->getWidgetHandler($uuid);

        return $widget->response();
    }
}
