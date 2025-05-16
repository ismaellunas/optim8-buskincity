<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\Setting;
use App\Services\UserService;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\SettingRequest;

class SettingController extends CrudController
{
    protected $title = 'Booking Settings';

    public function __construct(private UserService $userService)
    {}

    public function edit()
    {
        $settings = Setting::whereIn('key', [
            'booking_email_new_booking',
            'booking_email_reminder',
            'booking_email_cancellation',
            'allowed_early_check_in',
            'check_in_radius',
            'booking_access_common_user',
            'booking_access_roles',
        ])->get()->pluck('value', 'key')->all();

        $settings = $this->settingToDecode($settings);

        Inertia::share('i18n', $this->translations());

        return Inertia::render('Booking::Settings', $this->getData([
            'bookingSettings' => $settings,
            'roleOptions' => $this->userService->getRoleOptions(),
        ]));
    }

    public function update(SettingRequest $request)
    {
        $inputs = $request->validated();

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
            ['value' => json_encode($inputs['check_in_radius'])]
        );

        Setting::updateOrCreate(
            [
                'key' => 'booking_access_common_user',
                'group' => 'booking'
            ],
            ['value' => $inputs['access_common_user']]
        );

        Setting::updateOrCreate(
            [
                'key' => 'booking_access_roles',
                'group' => 'booking'
            ],
            ['value' => json_encode($inputs['access_roles'])]
        );

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Setting')
        ]);

        return back();
    }

    private function translations(): array
    {
        return [
            'access_common_user' => __('Is a common user have access?'),
            'available_check_in' => __('Available check-in before'),
            'booking_cancellation' => __('Booking cancellation'),
            'booking_remainder' => __('Booking remainder'),
            'check_in_radius' => __('Check-in radius'),
            'check_in' => __('Check-in'),
            'choose_roles_note' => __('Choose the roles that can access the booking feature on the frontend.'),
            'choose_roles' => __('Choose roles'),
            'control_access' => __('Control access frontend'),
            'email' => __('Email'),
            'empty' => __('Empty'),
            'new_booking' => __('New booking'),
            'save' => __('Save'),
        ];
    }

    private function settingToDecode(array $settings): array
    {
        $settingsToDecode = [
            'check_in_radius',
            'booking_access_common_user',
            'booking_access_roles',
        ];

        foreach ($settings as $key => $value) {
            if (in_array($key, $settingsToDecode)) {
                $settings[$key] = json_decode($value);
            }
        }

        return $settings;
    }
}
