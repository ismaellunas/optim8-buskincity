<?php

namespace App\Console\Commands;

use App\Helpers\Url;
use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixUniqueKeyPageTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:unique-key-page-translation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix data on page translation for unique key field.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pageTranslations = PageTranslation::all();

        foreach ($pageTranslations as $pageTranslation) {
            if (!$pageTranslation->unique_key) {
                $isCodeExist = function ($code) {
                    return PageTranslation::where('unique_key', $code)->exists();
                };

                $pageTranslation->unique_key = Url::randomDigitSegment($isCodeExist);
                $pageTranslation->save();
            }
        }

        return 0;
    }
}
