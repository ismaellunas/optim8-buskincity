<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class GeneratePageStyle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:page-style';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate page style.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pages = Page::all();

        foreach ($pages as $page) {
            foreach ($page->translations as $pageTranslation) {
                $pageTranslation->updatePageStyle();
                $pageTranslation->save();
            }
        }

        return Command::SUCCESS;
    }
}
