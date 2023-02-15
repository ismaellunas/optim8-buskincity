<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\Setting;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\SettingRequest;

class SettingController extends CrudController
{
    protected $title = 'Booking Settings';

    public function edit()
    {
        $settings = Setting::whereIn('key', [
            'booking_email_new_booking',
            'booking_email_reminder',
            'booking_email_cancellation',
            'allowed_early_check_in',
            'check_in_radius',
        ])->get()->pluck('value', 'key')->all();

        $settings['check_in_radius'] = json_decode($settings['check_in_radius']);

        return Inertia::render('Booking::Settings', $this->getData([
            'bookingSettings' => $settings,
        ]));
    }

    public function update(SettingRequest $request)
    {
        $inputs = $request->all();

        Setting::updateOrCreate(
            ['key' => 'booking_email_new_booking'],
            ['value' => $inputs['email_new_booking']]
        );

        Setting::updateOrCreate(
            ['key' => 'booking_email_reminder'],
            ['value' => $inputs['email_reminder']]
        );

        Setting::updateOrCreate(
            ['key' => 'booking_email_cancellation'],
            ['value' => $inputs['email_cancellation']]
        );

        Setting::updateOrCreate(
            [
                'key' => 'allowed_early_check_in',
                'group' => 'booking'
            ],
            ['value' => $inputs['allowed_early_check_in']]
        );

        Setting::updateOrCreate(
            [
                'key' => 'check_in_radius',
                'group' => 'booking'
            ],
            ['value' => $inputs['check_in_radius']]
        );

        $this->generateFlashMessage('Setting has been updated');

        return back();
    }
}
