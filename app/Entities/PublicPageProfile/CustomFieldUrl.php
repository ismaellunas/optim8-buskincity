<?php

namespace App\Entities\PublicPageProfile;

use App\Contracts\PublicPageProfileUrlInterface;
use App\Services\SettingService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CustomFieldUrl extends DefaultUrl implements PublicPageProfileUrlInterface
{
    protected function getSlug(): string
    {
        $setting = collect(
                app(SettingService::class)->getPublicPageProfileSlugCustomField()
            )
            ->firstWhere('role_id', $this->user->role_id);

        $field = Arr::get($setting, 'field', null);

        $value = $this->user->fullName;

        if ($field) {
            $metaValue = Arr::get(
                $this->user->getMetas([$field])->all(),
                $field,
                null
            );

            $value = $metaValue ?? $value;
        }


        return Str::slug($value);
    }
}