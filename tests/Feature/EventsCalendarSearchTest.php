<?php

namespace Tests\Feature;

use App\Enums\PublishingStatus;
use App\Models\GlobalOption;
use App\Services\CountryService;
use Database\Seeders\CountryTestSeeder;
use Database\Seeders\GlobalOptionSeeder;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Entities\EventCalendar;
use Modules\Booking\Services\EventsCalendarService;
use Modules\Space\Entities\Space;
use Tests\TestCase;

class EventsCalendarSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ModuleSeeder::class);
        $this->seed(CountryTestSeeder::class);
        $this->seed(GlobalOptionSeeder::class);
    }

    /** @test */
    public function country_service_normalizes_alpha3_to_alpha2(): void
    {
        $service = app(CountryService::class);

        $this->assertSame('NL', $service->toAlpha2('NLD'));
        $this->assertSame('NL', $service->toAlpha2('nl'));
        $this->assertSame('NLD', $service->toAlpha3('NL'));
    }

    /** @test */
    public function events_calendar_includes_published_space_events(): void
    {
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $space = Space::create([
            'name' => 'Amsterdam Arena',
            'type_id' => $cityTypeId,
            'city' => 'Amsterdam',
            'country_code' => 'NLD',
            'latitude' => 52.3676,
            'longitude' => 4.9041,
        ]);

        DB::table('space_events')->insert([
            'space_id' => $space->id,
            'title' => 'Summer Festival',
            'started_at' => now()->addDay(),
            'ended_at' => now()->addDays(2),
            'status' => PublishingStatus::PUBLISHED->value,
            'is_same_address_as_parent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $results = app(EventsCalendarService::class)->getRecords(15, [
            'dateRange' => [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ],
        ]);

        $types = $results->getCollection()->pluck('type')->all();

        $this->assertContains('space_event', $types);
    }

    /** @test */
    public function events_calendar_country_filter_matches_alpha2_for_alpha3_backed_rows(): void
    {
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $space = Space::create([
            'name' => 'Rotterdam Stage',
            'type_id' => $cityTypeId,
            'city' => 'Rotterdam',
            'country_code' => 'NLD',
            'latitude' => 51.9244,
            'longitude' => 4.4777,
        ]);

        DB::table('space_events')->insert([
            'space_id' => $space->id,
            'title' => 'Harbour Show',
            'started_at' => now()->addDay(),
            'ended_at' => now()->addDays(2),
            'status' => PublishingStatus::PUBLISHED->value,
            'is_same_address_as_parent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $viewRecord = EventCalendar::where('title', 'Harbour Show')->first();

        $this->assertSame('NL', $viewRecord->country_code);

        $results = app(EventsCalendarService::class)->getRecords(15, [
            'country' => 'NL',
            'dateRange' => [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ],
        ]);

        $this->assertTrue(
            $results->getCollection()->contains(fn ($record) => $record['title'] === 'Harbour Show')
        );
    }

    /** @test */
    public function events_calendar_exposes_is_special_event_flag(): void
    {
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $space = Space::create([
            'name' => 'Utrecht Square',
            'type_id' => $cityTypeId,
            'city' => 'Utrecht',
            'country_code' => 'NL',
            'latitude' => 52.0907,
            'longitude' => 5.1214,
        ]);

        DB::table('space_events')->insert([
            'space_id' => $space->id,
            'title' => 'City Event',
            'started_at' => now()->addDay(),
            'ended_at' => now()->addDays(2),
            'status' => PublishingStatus::PUBLISHED->value,
            'is_same_address_as_parent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $record = EventCalendar::where('title', 'City Event')->first();

        $this->assertFalse((bool) $record->is_special_event);

        $payload = $record->eventData();

        $this->assertArrayHasKey('is_special_event', $payload);
        $this->assertFalse($payload['is_special_event']);
    }
}
