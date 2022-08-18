<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\Setting;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\SettingRequest;

class SettingController extends CrudController
{
    public function edit()
    {
        $settings = Setting::whereIn('key', [
            'booking_email_new_booking',
            'booking_email_reminder',
            'booking_email_cancellation',
        ])->get()->pluck('value', 'key');

        return Inertia::render('Booking::Settings', $this->getData([
            'title' => 'Booking Settings',
            'bookingSettings' => $settings,
        ]));
    }

    public function update(SettingRequest $request)
    {
        $inputs = $request->all();

        Setting::updateOrCreate(
            ['key' => 'booking_email_new_booking'],
            ['order' => 0, 'value' => $inputs['email_new_booking']]
        );

        Setting::updateOrCreate(
            ['key' => 'booking_email_reminder'],
            ['order' => 0, 'value' => $inputs['email_reminder']]
        );

        Setting::updateOrCreate(
            ['key' => 'booking_email_cancellation'],
            ['order' => 0, 'value' => $inputs['email_cancellation']]
        );

        $this->generateFlashMessage('Setting has been updated');

        return back();
    }
}
