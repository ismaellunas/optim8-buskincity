<?php

namespace App\Jobs;

use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class CompileThemeCss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SettingService $settingService)
    {
        $settingService->generateVariablesSass();

        $settingService->generateThemeCss();

        $folderPrefix = !App::environment('production')
            ? config('app.env')
            : null;

        $assetFrontend = $settingService->uploadThemeCssFrontend($folderPrefix);
        $settingService->saveCssUrlFrontend($assetFrontend->fileUrl);

        $assetBackend = $settingService->uploadThemeCssBackend($folderPrefix);
        $settingService->saveCssUrlBackend($assetBackend->fileUrl);

        $settingService->clearStorageTheme();
    }
}
