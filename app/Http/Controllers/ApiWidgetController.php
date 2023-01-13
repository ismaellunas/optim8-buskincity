<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;

class ApiWidgetController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getLatestRegistrations(Request $request)
    {
        $scopes = [
            'inRoles' => $request->roles ?? null,
        ];

        return [
            'records' => $this->userService->getLatestRegistrations(
                auth()->user(),
                $request->term,
                $scopes,
            ),
        ];
    }
}
