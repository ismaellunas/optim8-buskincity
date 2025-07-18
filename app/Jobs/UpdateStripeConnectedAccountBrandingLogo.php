<?php

namespace App\Jobs;

use App\Entities\UserMetaStripe;
use App\Models\UserMeta;
use App\Services\{
    StripeSettingService,
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
    public function __construct()
    {
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

        $logoMedia = app(StripeSettingService::class)->logoMedia();

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
                ->uploadBusinessLogo($this->fileResource);
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

        $userMetaStripe = new UserMetaStripe($stripeAccountIdUserMeta->user);
        $userMetaStripe->setAccount($stripeAccount);
    }
}
