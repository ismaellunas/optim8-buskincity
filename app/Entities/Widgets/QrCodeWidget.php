<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\ProfileQrCode;
use App\Services\SettingService;

class QrCodeWidget extends BaseWidget implements WidgetInterface
{
    protected $component = 'QrCode';

    protected function getData(): array
    {
        return [
            'logoThumbnailUrl' => app(SettingService::class)->qrCodePublicPageLogo(),
            'logoUrl' => app(SettingService::class)->qrCodeHighResolutionLogo(),
            'dimension' => [
                'default' => [
                    'width' => 128,
                    'height' => 128,
                ],
            ],
            'name' => $this->user->qr_code_logo_name,
            'uniqueKey' => $this->user->unique_key,
            'qrOptions' => (
                    new ProfileQrCode(
                        $this->user, 2480,
                        $this->storedSetting['setting']['query_parameter'] ?? []
                    )
                )->options(),
        ];
    }

    public function canBeAccessed(): bool
    {
        $qrCodeIsDisplayed = app(SettingService::class)->qrCodePublicPageIsDisplayed();
        $canPublicPage = $this->user->hasPublicPage;

        return parent::canBeAccessed()
            && (
                $canPublicPage
                && $qrCodeIsDisplayed
            );
    }
}
