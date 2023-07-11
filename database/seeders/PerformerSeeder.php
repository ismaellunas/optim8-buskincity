<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use App\Services\GlobalOptionService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class PerformerSeeder extends Seeder
{
    private $languageId = null;
    private $countryCode = null;
    private $domain = null;

    public function __construct()
    {
        $this->languageId = Language::where('code', 'en')->value('id');
        $this->countryCode = Setting::key('default_country')->value('value');
        $this->domain = config('constants.domain');
    }

    public function run()
    {
        $disciplines = app(GlobalOptionService::class)->getDisciplineOptions();
        $countries = Country::select('alpha2')->get();

        $performers = User::factory()
            ->count(2)
            ->state(new Sequence(
                [
                    'first_name' => 'Dan',
                    'last_name' => 'Rice',
                    'email' => 'dan.rice@'.$this->domain,
                    'language_id' => $this->languageId,
                ],
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@'.$this->domain,
                    'language_id' => $this->languageId,
                ],
            ))
            ->create();

        foreach ($performers as $performer) {
            $performer->setMeta('country', $this->countryCode);
            $performer->setMeta('discipline', $disciplines->random()['id']);
            $performer->saveMetas();

            $performer->assignRole('Performer');
        }

        $anotherPerformer = User::factory()
            ->count(10)
            ->state(new Sequence(
                fn () => ['language_id' => $this->languageId],
            ))
            ->create();

        foreach ($anotherPerformer as $performer) {
            $performer->setMeta('country', $countries->random()->alpha2);
            $performer->setMeta('discipline', $disciplines->random()['id']);
            $performer->saveMetas();

            $performer->assignRole('Performer');
        }
    }
}
