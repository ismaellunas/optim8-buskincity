<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;

class MigrateCityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:city-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing free-text city data to the new cities table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting city data migration...');

        $this->migrateSpaces();
        $this->migrateSpaceEvents();

        $this->info('City data migration completed.');

        return 0;
    }

    private function migrateSpaces()
    {
        $this->info('Migrating Spaces...');
        
        $spaces = Space::whereNotNull('city')
            ->whereNull('city_id')
            ->whereNotNull('country_code')
            ->get();

        $bar = $this->output->createProgressBar($spaces->count());
        $bar->start();

        foreach ($spaces as $space) {
            $city = $this->findCity($space->city, $space->country_code);

            if ($city) {
                $space->city_id = $city->id;
                $space->save();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateSpaceEvents()
    {
        $this->info('Migrating Space Events...');

        $events = SpaceEvent::whereNotNull('city')
            ->whereNull('city_id')
            ->whereNotNull('country_code')
            ->get();

        $bar = $this->output->createProgressBar($events->count());
        $bar->start();

        foreach ($events as $event) {
            $city = $this->findCity($event->city, $event->country_code);

            if ($city) {
                $event->city_id = $city->id;
                $event->save();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function findCity($name, $countryCode)
    {
        // Try exact match
        $city = City::where('country_code', $countryCode)
            ->where('name', $name)
            ->first();

        if ($city) {
            return $city;
        }

        // Try case-insensitive match
        $city = City::where('country_code', $countryCode)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        return $city;
    }
}
