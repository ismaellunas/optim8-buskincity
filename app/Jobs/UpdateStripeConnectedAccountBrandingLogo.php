<?php

namespace App\Jobs;

use App\Models\{
    Media,
    UserMeta,
};
use App\Services\{
    SettingService,
    StripeService,
};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateStripeConnectedAccountBrandingLogo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fileResource;
    private $logoMedia;
    private $stripeService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Media $logoMedia)
    {
        $this->logoMedia = $logoMedia;

        $this->stripeService = app(StripeService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stripeAccountUserMetas = UserMeta::key('stripe_account_id')
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'updated_at');
                },
            ])
            ->select('id', 'value', 'user_id')
            ->get();

        $logoMedia = app(SettingService::class)->getLogoMedia();

        if ($logoMedia) {
            $this->fileResource = $this
                ->stripeService
                ->getLogoFileFromCloud($logoMedia);
        }

        $stripeAccountUserMetas->each([$this, 'updateBrandingLogoAndUserMeta']);
    }

    public function updateBrandingLogoAndUserMeta(UserMeta $stripeAccountIdUserMeta)
    {
        $file = null;

        if ($this->fileResource) {
            $file = $this
                ->stripeService
                ->uploadBusinesLogo($this->fileResource);
        }

        $stripeAccount = $this->stripeService->updateAccount(
            $stripeAccountIdUserMeta->value,
            [
                'settings' => [
                    'branding' => [
                        'logo' => $file ? $file->id : null,
                    ]
                ],
            ]
        );

        $this->stripeService->setUserStripeAccount(
            $stripeAccountIdUserMeta->user,
            $stripeAccount
        );
    }
}
