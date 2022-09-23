<?php

namespace App\Http\Controllers;

use App\Entities\Caches\SettingCache;
use App\Http\Requests\SettingKeyRequest;
use App\Models\Setting;
use App\Services\SettingService;
use App\Traits\FlashNotifiable;
use Inertia\Inertia;

class SettingKeyController extends Controller
{
    use FlashNotifiable;

    private $settingService;
    private $baseRouteName = 'admin.settings.keys';

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        $keys = $this->settingService->getKeys();

        return Inertia::render('SettingKey', [
            'title' => __('Keys'),
            'baseRouteName' => $this->baseRouteName,
            'keys' => $keys,
            'groups' => array_keys($keys),
        ]);
    }

    public function update(SettingKeyRequest $request)
    {
        $inputs = $request->validated();
        $settingKeys = collect(config('constants.settings.keys'));

        foreach ($inputs as $key => $value) {
            $setting = $settingKeys->where('key', $key)->first();

            if ($setting) {
                $setting['value'] = $value;

                Setting::updateOrCreate([
                    'key' => $key
                ],$setting);
            }
        }

        app(SettingCache::class)->flush();

        $this->generateFlashMessage('Keys updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
