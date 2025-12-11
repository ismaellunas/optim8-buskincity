<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonPath = database_path('seeders/data/cities.json');
        
        if (!\Illuminate\Support\Facades\File::exists($jsonPath)) {
            $this->command->error("Cities JSON file not found at: $jsonPath");
            return;
        }

        $cities = json_decode(\Illuminate\Support\Facades\File::get($jsonPath), true);
        
        $this->command->info("Seeding " . count($cities) . " cities...");

        $now = now();
        $uniqueCities = [];
        foreach ($cities as $city) {
            $key = $city['country_code'] . '-' . $city['name'];
            if (!isset($uniqueCities[$key])) {
                $city['created_at'] = $now;
                $city['updated_at'] = $now;
                $uniqueCities[$key] = $city;
            }
        }
        $cities = array_values($uniqueCities);

        $chunks = array_chunk($cities, 1000);
        
        foreach ($chunks as $chunk) {
            City::upsert(
                $chunk, 
                ['name', 'country_code'], 
                ['latitude', 'longitude', 'state_code', 'updated_at']
            );
        }
        
        $this->command->info("Cities seeded successfully!");
    }
}
