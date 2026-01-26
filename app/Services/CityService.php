<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\Log;

class CityService
{
    /**
     * Find or create a city by name and country code
     * 
     * @param string $name
     * @param string $countryCode
     * @param float|null $latitude
     * @param float|null $longitude
     * @return City
     */
    public function findOrCreate(string $name, string $countryCode, ?float $latitude = null, ?float $longitude = null): City
    {
        // Try to find existing city (case-insensitive)
        $city = City::where('country_code', $countryCode)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($city) {
            // Update coordinates if provided and not already set
            if ($latitude && $longitude && !$city->latitude && !$city->longitude) {
                $city->update([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            }
            return $city;
        }

        // Create new city
        try {
            $city = City::create([
                'name' => $name,
                'country_code' => $countryCode,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            Log::info("Created new city: {$name}, {$countryCode}");

            return $city;
        } catch (\Exception $e) {
            // If creation fails (e.g., unique constraint), try to find again
            $city = City::where('country_code', $countryCode)
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->first();

            if ($city) {
                return $city;
            }

            // If still not found, re-throw the exception
            throw $e;
        }
    }

    /**
     * Find a city by name and country code (case-insensitive)
     * 
     * @param string $name
     * @param string $countryCode
     * @return City|null
     */
    public function find(string $name, string $countryCode): ?City
    {
        return City::where('country_code', $countryCode)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    /**
     * Search cities by name
     * 
     * @param string $search
     * @param string|null $countryCode
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $search, ?string $countryCode = null, int $limit = 50)
    {
        $query = City::query();

        $query->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('country_code', 'ilike', "%{$search}%");
        });

        if ($countryCode) {
            $query->where('country_code', $countryCode);
        }

        return $query->limit($limit)->get();
    }
}
