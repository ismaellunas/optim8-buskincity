<?php

namespace App\Services;

use App\Models\City;
use App\Models\Location;
use Illuminate\Support\Str;

class LocationService
{
    /**
     * Find or create a canonical Location from pitch meta / form location payload.
     *
     * @param  array<string, mixed>  $locationData
     */
    public function findOrCreateFromPitchData(
        City $city,
        array $locationData,
        ?int $spaceId = null
    ): Location {
        $address = trim((string) ($locationData['address'] ?? ''));
        $name = $this->deriveName($city, $address);

        $query = Location::query()->where('city_id', $city->id);

        if ($address !== '') {
            $query->whereRaw('LOWER(address) = ?', [strtolower($address)]);
        } else {
            $query->whereRaw('LOWER(name) = ?', [strtolower($name)]);
        }

        if ($spaceId) {
            $existing = Location::where('space_id', $spaceId)->first();

            if ($existing) {
                return $existing;
            }
        }

        $location = $query->first();

        if ($location) {
            if ($spaceId && ! $location->space_id) {
                $location->space_id = $spaceId;
                $location->save();
            }

            return $location;
        }

        return Location::create([
            'city_id' => $city->id,
            'name' => $name,
            'address' => $address !== '' ? $address : null,
            'latitude' => isset($locationData['latitude']) ? (float) $locationData['latitude'] : null,
            'longitude' => isset($locationData['longitude']) ? (float) $locationData['longitude'] : null,
            'space_id' => $spaceId,
        ]);
    }

    private function deriveName(City $city, string $address): string
    {
        if ($address !== '') {
            return Str::limit($address, 128, '');
        }

        return Str::limit($city->name.' location', 128, '');
    }
}
