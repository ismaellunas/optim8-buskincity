<?php

namespace App\Jobs;

use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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
        try {
            $this->settingService->generateVariablesSass();

            Log::info('Job - Generate variable Sass (Pass)');

            $this->settingService->generateThemeCss();

            Log::info('Job - Generate Theme Css (Pass)');

            $asset = $this->settingService->uploadThemeCssToCloudStorage(
                !App::environment('production')
                ? config('app.env')
                : null
            );

            Log::info('Job - Upload Theme Css To Cloud Storage (Pass)');
            Log::debug('asset url: ' . $asset->fileUrl);

            $this->settingService->saveCssUrl($asset->fileUrl);

            Log::info('Job - Save Css Url (Pass)');

            $isCleared = $this->settingService->clearStorageTheme();

            Log::info('Job - Clear Storage Theme (Pass)');
            Log::debug('Is storage clear? '.$isCleared);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
