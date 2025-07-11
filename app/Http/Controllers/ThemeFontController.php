<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeFontRequest;
use App\Jobs\CompileThemeCss;
use App\Models\Setting;
use App\Services\SettingService;
use Inertia\Inertia;

class ThemeFontController extends CrudController
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
        $googleApiKey = $this->settingService->getGoogleApi();
        $defaultFontSizes = config('constants.theme_font_sizes');

        return Inertia::render(
            'ThemeFonts',
            $this->getData([
                'uppercaseOptions' => $this->settingService->getUppercaseTextOptions(),
                'uppercaseText' => $this->settingService->getUppercaseTexts(),
                'contentParagraphWidth' => $this->settingService->getContentParagraphWidth(),
                'headingsFont' => $this->settingService->getFont('headings_font'),
                'mainTextFont' => $this->settingService->getFont('main_text_font'),
                'buttonsFont' => $this->settingService->getFont('buttons_font'),
                'webfontsUrl' => 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key='.$googleApiKey,
                'baseUrlGoogleFont' => 'https://fonts.googleapis.com/css2',
                'defaultFontSizes' => $defaultFontSizes,
                'fontSizes' => $this->settingService->getFontSizes(),
                'i18n' => $this->translations(),
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

        foreach ($inputs['sizes'] as $key => $fontSize) {
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = $fontSize;
            $setting->save();
        }

        CompileThemeCss::dispatch();

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    private function translations(): array
    {
        return [
            'typography' => __('Typography'),
            'save' => __('Save'),
            'uppercase_text' => __('Uppercase text'),
            'content_paragraph_width' => __('Content paragraph width'),
            'heading_font' => __('Heading font'),
            'font_family' => __('Font family'),
            'font_weight' => __('Font weight'),
            'font_style' => __('Font style'),
            'preview' => __('Preview'),
            'main_text_font' => __('Main text font'),
            'button_font' => __('Buttons font'),
            'sizes' => __('Sizes'),
        ];
    }
}
