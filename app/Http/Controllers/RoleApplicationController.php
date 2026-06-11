<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleApplicationRequest;
use App\Services\RoleApplicationService;
use App\Services\SettingService;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleApplicationController extends Controller
{
    use FlashNotifiable;

    public function __construct(private RoleApplicationService $roleApplicationService)
    {
    }

    public function create(Request $request)
    {
        $role = $request->query('role', config('permission.role_names.city_admin'));

        if (! in_array($role, $this->roleApplicationService->allowedRoles(), true)) {
            abort(404);
        }

        $user = auth()->user();

        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();

        return Inertia::render('RoleApplication/Apply', [
            'requestedRole' => $role,
            'roleLabel' => $this->roleLabel($role),
            'requiresPassword' => $this->roleApplicationService->requiresPasswordOnSubmit($role),
            'requiresCountrySpace' => $this->roleApplicationService->requiresCountrySpaceOnSubmit($role),
            'countrySpaceOptions' => $this->roleApplicationService->countrySpaceOptionsForApplication(),
            'recaptchaSiteKey' => $recaptchaKeys['recaptcha_site_key'] ?? null,
            'defaults' => [
                'email' => $user?->email,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
            ],
            'title' => __('Apply as :role', ['role' => $this->roleLabel($role)]),
        ]);
    }

    public function store(StoreRoleApplicationRequest $request)
    {
        $this->roleApplicationService->submit(
            $request->validated(),
            $request->file('logo'),
            $request->file('cover')
        );

        $this->generateFlashMessage(__('Your application has been submitted and is pending review.'));

        return redirect()->route('role-applications.submitted');
    }

    public function submitted()
    {
        return Inertia::render('RoleApplication/Submitted', [
            'title' => __('Application submitted'),
        ]);
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            config('permission.role_names.special_events_admin') => __('Special Events Administrator'),
            default => __('City Administrator'),
        };
    }
}
