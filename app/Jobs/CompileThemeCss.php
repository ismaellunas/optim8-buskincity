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

    private $settingService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->settingService = app(SettingService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->settingService->generateVariablesSass();

        $this->settingService->generateThemeCss();

        $asset = $this->settingService->uploadThemeCssToCloudStorage(
            !App::environment('production')
            ? config('app.env')
            : null
        );

        $this->settingService->saveCssUrl($asset->fileUrl);

        $this->settingService->clearStorageTheme();
    }
}
