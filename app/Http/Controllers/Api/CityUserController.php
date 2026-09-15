<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\InScopedCityIds;
use Illuminate\Http\Request;

class CityUserController extends Controller
{
    public function index(User $user)
    {
        return response()->json($user->assignedScopeCities());
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'cities' => ['array', new InScopedCityIds()],
            'cities.*' => 'exists:cities,id',
        ];

        $request->validate($rules);

        $cities = $request->input('cities', []);

        if ($user->isSpecialEventsAdmin() || $user->isCityAdministrator()) {
            $user->syncCitiesForCurrentRole($cities);
        } else {
            return response()->json([
                'message' => 'Cities can only be assigned to a city-scoped role.',
            ], 422);
        }

        return response()->json([
            'message' => 'Cities updated successfully',
            'cities' => $user->fresh()->assignedScopeCities(),
        ]);
    }
}
