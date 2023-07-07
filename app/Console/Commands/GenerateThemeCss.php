<?php

namespace App\Console\Commands;

use App\Services\ThemeService;
use Illuminate\Console\Command;
use \Exception;

class GenerateThemeCss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:theme-css';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile and store CSS into Database and css files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $themeService = app(ThemeService::class);

        try {
            $themeService->generateVariablesSass();

            $themeService->generateCss();

            $themeService->storeCssToSettingTable();

            $themeService->createCssFiles();

        } catch (Exception $e) {

            throw $e;

        } finally {

            $themeService->clearStorageTheme();
        }

        return Command::SUCCESS;
    }
}
