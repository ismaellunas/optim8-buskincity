<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class FixThemeFontSizeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:theme-font-size-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update data can have a multiple device (Desktop, tablet, and mobile).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $defaultFontSizes = config('constants.theme_font_sizes');

        $fontSizes = Setting::whereIn('key', [
                'font_size_heading_1',
                'font_size_heading_2',
                'font_size_heading_3',
                'font_size_heading_4',
                'font_size_heading_5',
                'font_size_heading_6',
                'font_size_small',
                'font_size_text',
            ])
            ->get();

        foreach ($fontSizes as $fontSize) {
            $value = $fontSize->value;

            if (is_string($value)) {
                $value = [
                    'desktop' => $fontSize->value,
                    'tablet' => $defaultFontSizes[$fontSize->key]['tablet'],
                    'mobile' => $defaultFontSizes[$fontSize->key]['mobile'],
                ];

                $fontSize->value = $value;
                $fontSize->save();
            }
        }

        return Command::SUCCESS;
    }
}
