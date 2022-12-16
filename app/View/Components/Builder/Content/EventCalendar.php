<?php

namespace App\View\Components\Builder\Content;

use App\Services\ModuleService;
use App\Services\SettingService;
use Exception;

class EventCalendar extends BaseContent
{
    private $moduleName = 'Booking';

    public $googleApiKey;
    public $initPosition;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        if (!$this->isModuleActive()) {
            throw new Exception(__("{$this->moduleName} module is not activated!"));
        }

        $this->googleApiKey = app(SettingService::class)->getGoogleApi();

        $this->initPosition = [
            'latitude' => 59.3260668,
            'longitude' => 17.8419716
        ];
    }

    private function isModuleActive(): bool
    {
        return app(ModuleService::class)->isModuleActive($this->moduleName);
    }
}
