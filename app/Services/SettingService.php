<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    public static function getFrontendCssUrl(): string
    {
        $urlCss = Setting::where('key', 'url_css')->first(['key', 'value']);

        return $urlCss->value ?? mix('css/app.css')->toHtml();
    }

    public function generateVariablesSass()
    {
        $colors = Setting::where('group', 'theme_color')
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->all();

        $variablesSass = view('theme_options.colors_sass', array_merge(
            config('constants.theme_colors'),
            $colors
        ));

        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('theme/sass'),
        ]);

        $disk->put('sdb_variables.sass', $variablesSass);
    }

    public function generateThemeCss()
    {
        exec('cd .. && npx webpack --config sdb.webpack.config.js');
    }
}
