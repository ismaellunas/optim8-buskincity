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
        return response()->json($user->adminCities);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'cities' => 'array',
            'cities.*' => 'exists:cities,id',
        ]);

        $user->adminCities()->sync($request->input('cities', []));

        return response()->json(['message' => 'Cities updated successfully', 'cities' => $user->fresh()->adminCities]);
    }
}
