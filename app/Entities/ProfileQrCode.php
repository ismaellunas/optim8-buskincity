<?php

namespace App\Entities;

use App\Services\SettingService;
use App\Helpers\Color;

class ProfileQrCode
{
    private $settingService;
    private array $nameLines = [];
    private string $quietZoneColor = '#ffffff';
    private float $dimentionRatio;
    private float $tenPercent;

    private array $defaults = [
        'fontSize' => 50,
        'titleTop' => 50,
        'subTitleTop' => 105,
        'dimension' => 620,
    ];

    public function __construct(
        private $user,
        private $dimension = 620,
        private $queryParameter = [],
        private $nameAttribute = 'stage_name',
    ) {
        $this->settingService = app(SettingService::class);
        $this->setNameLines();
        $this->setDimensionRatio();
        $this->setTenPercent();
    }

    private function setDimensionRatio(): void
    {
        $this->dimentionRatio = $this->dimension / $this->defaults['dimension'];
    }

    private function setTenPercent(): void
    {
        $this->tenPercent = $this->dimension / 10;
    }

    private function setNameLines(): void
    {
        $name = $this->user->metas->firstWhere('key', $this->nameAttribute)->value
            ?? $this->user->fullName;

        $this->nameLines = explode("\n", wordwrap($name, 22));
    }

    private function primaryColor(): string
    {
        $colors = $this->settingService->getColors();
        $defaultBgColor = '#ffffff';

        return !empty($colors['color_primary'])
            ? $colors['color_primary']->value ?? $defaultBgColor
            : $defaultBgColor;
    }

    private function isNameMultiline(): bool
    {
        return count($this->nameLines) > 1;
    }

    private function fontSize(): int
    {
        return round($this->defaults['fontSize'] * $this->dimentionRatio);
    }

    private function font(): string
    {
        return 'bold '. $this->fontSize() .'px Arial';
    }

    private function titleHeight(): int
    {
        return round($this->tenPercent * 3 - $this->dimension * 0.02);
    }

    private function titleTop(): int
    {
        return $this->isNameMultiline()
            ? $this->tenPercent
            : $this->tenPercent + $this->tenPercent / 2;
    }

    private function subTitleTop(): int
    {
        return round($this->tenPercent * 2);
    }

    public function options(): array
    {
        $fontColor = Color::getTextColorFromBackground($this->primaryColor());

        $font = $this->font();

        $text = $this->user->getProfilePageUrlAttribute($this->queryParameter);

        return [
            'width' => $this->dimension,
            'height' => $this->dimension,
            'text' => $text,
            'title' => $this->nameLines[0] ?? '',
            'titleBackgroundColor' => $this->primaryColor(),
            'titleColor' => $fontColor,
            'titleFont' => $font,
            'titleHeight' => $this->titleHeight(),
            'titleTop' => $this->titleTop(),
            'subTitle' => $this->nameLines[1] ?? '',
            'subTitleColor' => $fontColor,
            'subTitleFont' => $this->isNameMultiline()
                ? $font
                : 'normal normal normal 14px Arial',
            'subTitleTop' => $this->subTitleTop(),
            'quietZone' => $this->dimension * 0.02,
            'quietZoneColor' => $this->quietZoneColor,
        ];
    }
}
