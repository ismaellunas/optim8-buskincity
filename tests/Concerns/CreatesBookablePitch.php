<?php

namespace Tests\Concerns;

use App\Models\City;
use App\Models\Role;
use App\Models\User;
use App\Services\LocationService;
use App\Services\LoginService;
use Carbon\Carbon;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Modules\Booking\Database\Seeders\SettingTableSeeder;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Database\Seeders\DefaultSeeder as EcommerceDefaultSeeder;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;

trait CreatesBookablePitch
{
    protected function seedEventsOverhaulDependencies(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(EcommerceDefaultSeeder::class);
        $this->seed(ModuleSeeder::class);
        $this->seed(SettingTableSeeder::class);
    }

    protected function withoutFrontendBookingMiddleware(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);
    }

    protected function actingAsPerformerOnUserPortal(?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        $user->assignRole(config('permission.role_names.performer'));

        LoginService::setUserHomeUrl();
        $this->actingAs($user);

        return $user;
    }

    /**
     * Published pitch with schedule and pitch window — intentionally **no** admin ProductEvent.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function createPublishedPitchWithoutProductEvent(array $overrides = []): Product
    {
        $pitchStart = ($overrides['pitch_start'] ?? now()->addWeek()->startOfWeek(Carbon::MONDAY))->copy();
        $pitchEnd = ($overrides['pitch_end'] ?? $pitchStart->copy()->addDays(13))->copy();
        $pitchName = $overrides['name'] ?? 'Direct Book Pitch';

        $city = City::factory()->create([
            'name' => $overrides['city_name'] ?? 'Orebro',
            'country_code' => $overrides['country_code'] ?? 'SWE',
        ]);

        $locationData = [
            'city' => $city->name,
            'country_code' => 'SE',
            'address' => 'Test Street 1',
            'latitude' => 59.27,
            'longitude' => 15.21,
        ];

        $location = app(LocationService::class)->findOrCreateFromPitchData($city, $locationData);

        $productType = ProductType::where('name', 'Event')->firstOrFail();
        $performerRole = Role::findByName('Performer');

        $product = Product::create([
            'product_type_id' => $productType->id,
            'status' => ProductStatus::PUBLISHED->value,
            'city_id' => $city->id,
            'location_id' => $location->id,
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text($pitchName),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text('Pitch for direct booking overhaul tests'),
                ])),
                'short_description' => new TranslatedText(collect([
                    'en' => new Text(''),
                ])),
            ],
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => TaxClass::getDefault()->id,
            'purchasable' => 'always',
            'shippable' => false,
            'stock' => 0,
            'backorder' => 0,
            'sku' => 'EVENT-'.$product->id,
        ]);

        $product->setMeta([
            'roles' => [$performerRole->id],
            'duration' => 60,
            'duration_unit' => 'minute',
            'bookable_date_range_type' => 'calendar_days_into_the_future',
            'bookable_date_range' => 14,
            'pitch_started_at' => $pitchStart->toDateString(),
            'pitch_ended_at' => $pitchEnd->toDateString(),
            'pitch_timezone' => 'UTC',
            'locations' => [$locationData],
        ]);
        $product->save();

        $schedule = Schedule::factory()->create([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
            'timezone' => 'UTC',
        ]);

        app(ProductEventService::class)->saveWeeklyHours(
            $this->weekdayWeeklyHours(),
            $schedule
        );

        return $product->fresh(['eventSchedule']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function weekdayWeeklyHours(): array
    {
        $weeklyHours = [];

        for ($day = 1; $day <= 7; $day++) {
            $weeklyHours[$day] = [
                'day' => $day,
                'is_available' => $day <= 5,
                'hours' => $day <= 5
                    ? [['started_time' => '09:00', 'ended_time' => '17:00']]
                    : [],
            ];
        }

        return $weeklyHours;
    }

    protected function nextBookableWeekday(Carbon $withinPitchWindow): Carbon
    {
        $date = $withinPitchWindow->copy();

        while ($date->isWeekend()) {
            $date->addDay();
        }

        return $date;
    }
}
