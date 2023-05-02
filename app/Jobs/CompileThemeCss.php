<?php

namespace App\Jobs;

use App\Services\SettingService;
use App\Services\ThemeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use \Exception;

class CompileThemeCss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        ThemeService $themeService,
        SettingService $settingService
    ) {
        try {
            $themeService->generateVariablesSass();

            $themeService->generateCss();

            $uploadedCssFrontend = $themeService->uploadCssFrontend();
            $uploadedCssBackend = $themeService->uploadCssBackend();
            $uploadedCssEmail = $themeService->uploadCssEmail();

            $settingService->saveCssUrlFrontend($uploadedCssFrontend->fileUrl);
            $settingService->saveCssUrlBackend($uploadedCssBackend->fileUrl);
            $settingService->saveCssUrlEmail($uploadedCssEmail->fileUrl);

        } catch (Exception $e) {

            throw $e;

        } finally {

            $themeService->clearStorageTheme();
        }
    }
}
