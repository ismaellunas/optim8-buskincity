<?php

namespace App\View\Components\Builder\Content;

use App\Services\IPService;
use App\Services\SettingService;
use Modules\Booking\ModuleService as BookingModuleService;
use Modules\Booking\Services\EventsCalendarService;

class EventsCalendar extends BaseContent
{
    public $googleApiKey;
    public $isEnabled;
    public $initPosition;
    public $maxDate;
    public $minDate;
    public $userCity;
    public $userCountryCode;
    public $yearRange;
    public $urls;

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->isEnabled = app(EventsCalendarService::class)->isEnabled();

        if (! $this->isEnabled) {
            return;
        }

        $this->googleApiKey = app(SettingService::class)->getGoogleApi();

        $this->initPosition = BookingModuleService::centerCoordinate();

        $this->userCountryCode = app(IPService::class)->getCountryCode();

        $this->userCity = app(IPService::class)->getCity();

        $date = app(IPService::class)->getDateTime();

        $this->minDate = $date->toDateString();

        $this->maxDate = $date->copy()->addMonths(6)->toDateString();

        $this->yearRange = [
            $date->format('Y'),
            $date->copy()->addYear()->format('Y')
        ];

        $this->urls = [
            'getEvents' => route('api.booking.events-calendar.index', [], false),
            'getLocationOptions' => route('api.booking.events-calendar.location-options', [], false),
        ];
    }
}
