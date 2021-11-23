<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeFontRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class ThemeFontController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.fonts';
    protected $title = 'Fonts';

    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        return Inertia::render(
            'ThemeFonts',
            $this->getData([
                'uppercaseOptions' => $this->settingService->getUppercaseTextOptions(),
                'uppercaseText' => $this->settingService->getUppercaseTexts(),
                'contentParagraphWidth' => $this->settingService->getContentParagraphWidth(),
                'headingsFont' => $this->settingService->getFont('headings_font'),
                'mainTextFont' => $this->settingService->getFont('main_text_font'),
                'buttonsFont' => $this->settingService->getFont('buttons_font'),
                'webfontsUrl' => 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key='.config('constants.google_api_key'),
                'baseUrlGoogleFont' => 'https://fonts.googleapis.com/css2',
            ])
        );
    }

    public function update(ThemeFontRequest $request)
    {
        $inputs = $request->validated();

        $uppercaseText = Setting::firstOrNew([
            'key' => 'uppercase_text',
        ]);

        $uppercaseText->value = collect($inputs['uppercase_text'])->toJson();
        $uppercaseText->group = 'typography';
        $uppercaseText->save();

        $contentParagraphWidth = Setting::firstOrNew([
            'key' => 'content_paragraph_width'
        ]);

        $contentParagraphWidth->value = $inputs['content_paragraph_width'];
        $contentParagraphWidth->group = 'typography';
        $contentParagraphWidth->save();

        $fontSettingKeys = [
            'headings_font',
            'main_text_font',
            'buttons_font',
        ];

        foreach ($fontSettingKeys as $fontSettingKey) {
            $font = Setting::firstOrNew(['key' => $fontSettingKey]);
            $font->value = json_encode([
                'family' => $inputs[$fontSettingKey.'_family'] ?? null,
                'weight' => $inputs[$fontSettingKey.'_weight'] ?? null,
                'style' => $inputs[$fontSettingKey.'_style'] ?? null,
            ]);
            $font->group = 'font';
            $font->save();
        }

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
