<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($this->searchQuery($request)->limit(50)->get());
    }

    public function search(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        return response()->json($this->searchQuery($request)->limit(50)->get());
    }

    private function searchQuery(Request $request)
    {
        $query = City::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('country_code', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('country_code')) {
            $query->where('country_code', $request->input('country_code'));
        }

        return $query->orderBy('name');
    }
}
