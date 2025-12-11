<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('country_code', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('country_code')) {
            $query->where('country_code', $request->input('country_code'));
        }

        return response()->json($query->limit(50)->get());
    }
}
