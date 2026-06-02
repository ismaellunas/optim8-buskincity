<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;

class CityUserController extends Controller
{
    public function index(User $user)
    {
        return response()->json($user->assignedScopeCities());
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'cities' => 'array',
            'cities.*' => 'exists:cities,id',
        ]);

        $cities = $request->input('cities', []);

        // Route the write to the correct scope role. Special Events Admins are
        // user_scope-only (no legacy city_user); City Admins dual-write.
        if ($user->isSpecialEventsAdmin()) {
            $user->syncScopeCities(config('permission.role_names.special_events_admin'), $cities);
        } else {
            $user->syncAdminCities($cities);
        }

        return response()->json([
            'message' => 'Cities updated successfully',
            'cities' => $user->fresh()->assignedScopeCities(),
        ]);
    }
}
