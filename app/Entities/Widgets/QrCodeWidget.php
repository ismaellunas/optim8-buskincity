<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\ProfileQrCode;
use App\Services\SettingService;

class QrCodeWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Your QR code";
    protected $componentName = 'QrCode';
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->data = $this->getWidgetData();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
        ];
    }

    private function getWidgetData(): array
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
            'text' => $this->user->profile_page_url,
            'uniqueKey' => $this->user->unique_key,
            'qrOptions' => (new ProfileQrCode($this->user, 2480))->options(),
            'description' => __(
                'Print your QR code and place it on your pitch. It will allow your audience to find you on :appName, send donations, book you for private gigs, or follow your work.',
                [
                    'appName' => config('app.name'),
                ]
            ),
        ];
    }

    public function canBeAccessed(): bool
    {
        $qrCodeIsDisplayed = app(SettingService::class)->qrCodePublicPageIsDisplayed();
        $canPublicPage = $this->user->hasPublicPage;

        return $canPublicPage && $qrCodeIsDisplayed;
    }
}
