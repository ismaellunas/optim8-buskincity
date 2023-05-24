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
            'i18n' => [
                'save' => __('Save'),
            ],
        ]);
    }

    public function update(SettingKeyRequest $request)
    {
        $inputs = $request->validated();
        $defaultKeys = collect(config('constants.settings.keys'));

        foreach ($inputs as $key => $value) {
            $defaultKey = $defaultKeys->where('key', $key)->first();

            if ($defaultKey) {
                $defaultKey['value'] = $value;

                Setting::updateOrCreate([
                    'key' => $key
                ],$defaultKey);
            }
        }

        app(SettingCache::class)->flush();

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Keys')
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function getTinyMCEKey(): ?string
    {
        return $this->settingService->getTinyMCEKey();
    }
}
