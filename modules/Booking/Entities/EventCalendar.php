<?php

namespace Modules\Booking\Entities;

use App\Helpers\GoogleMap;
use App\Models\BaseModel;
use App\Services\CountryService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EventCalendar extends BaseModel
{
    protected $table = 'event_calendars';

    protected $casts = [
        'geolocation' => 'array',
        'entity_ids' => 'array',
    ];

    public function scopeInType(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    public function scopeCity(Builder $query, string $city): void
    {
        $query->where('city', 'ILIKE', $city);
    }

    public function scopeCountry(Builder $query, string $countryCode): void
    {
        $query->where('country_code', 'ILIKE', $countryCode);
    }

    public function scopeDateRange(Builder $query, array $dates): void
    {
        if (empty($dates)) {
            return;
        }

        $dates = array_filter($dates);

        sort($dates);

        if (count($dates) == 1) {
            $query
                ->whereDate(DB::raw('started_at::DATE'), '<=', $dates[0])
                ->whereDate(DB::raw('ended_at::DATE'), '>=', $dates[0]);
        } else {
            $query->where(function ($query) use ($dates) {
                $query->where(function ($query) use ($dates) {
                    $query
                        ->whereDate(DB::raw('started_at::DATE'), '>=', $dates[0])
                        ->whereDate(DB::raw('started_at::DATE'), '<=', $dates[1]);
                });
                $query->orWhere(function ($query) use ($dates) {
                    $query
                        ->whereDate(DB::raw('ended_at::DATE'), '>=', $dates[0])
                        ->whereDate(DB::raw('ended_at::DATE'), '<=', $dates[1]);
                });
                $query->orWhere(function ($query) use ($dates) {
                    $query
                        ->whereDate(DB::raw('started_at::DATE'), '<=', $dates[0])
                        ->whereDate(DB::raw('ended_at::DATE'), '>=', $dates[1]);
                });
            });
        }
    }

    private function country(): string
    {
        return (
            ($this->country_code ?? false)
            ? app(CountryService::class)->getCountryName($this->country_code)
            : ""
        );
    }

    private function startedAt(): Carbon
    {
        return Carbon::parse($this->started_at);
    }

    private function endedAt(): Carbon
    {
        return Carbon::parse($this->ended_at);
    }

    private function isEndedOnTheSameDate(): bool
    {
        return $this->startedAt()->isSameDay($this->endedAt());
    }

    public function directionUrl(array $initGeoLocation, $defaultUrl = ''): string
    {
        if (
            !is_null(Arr::get($this->geolocation, 'latitude'))
            && !is_null(Arr::has($this->geolocation, 'longitude'))
        ) {
            return GoogleMap::directionUrl(
                $this->geolocation['latitude'],
                $this->geolocation['longitude'],
                $initGeoLocation['latitude'],
                $initGeoLocation['longitude']
            );
        }

        return $defaultUrl;
    }

    public function eventData(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country(),
            'geolocation' => $this->geolocation,
            'timezone' => $this->timezone,
            'is_ended_on_same_date' => $this->isEndedOnTheSameDate(),
            'started_datetime' => $this->started_at,
            'ended_datetime' => $this->started_at,
            'started_time' => $this->startedAt()->format('H:i'),
            'ended_time' => $this->endedAt()->format('H:i'),
            'formated_started_date' => $this->startedAt()->format('d M Y'),
            'formated_ended_date' => $this->endedAt()->format('d M Y'),
        ];
    }
}
