<?php

namespace App\Jobs;

use App\Entities\UserMetaStripe;
use App\Models\UserMeta;
use App\Services\{
    StripeService,
    StripeSettingService,
};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateStripeConnectedAccountColor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $colorPrimary;
    private $colorSecondary;
    private $stripeService;

    public function __construct(
        ?string $colorPrimary = null,
        ?string $colorSecondary = null
    ) {
        $stripeSettingService = app(StripeSettingService::class);

        $this->colorPrimary = $colorPrimary ?? $stripeSettingService->defaultPrimaryColor();
        $this->colorSecondary = $colorSecondary ?? $stripeSettingService->defaultSecondaryColor();

        $this->stripeService = app(StripeService::class);
    }

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

        $stripeAccountUserMetas->each([$this, 'updateColorsAndUserMeta']);
    }

    public function updateColorsAndUserMeta(UserMeta $stripeAccountIdUserMeta)
    {
        $stripeAccount = $this->stripeService->updateAccount(
            $stripeAccountIdUserMeta->value,
            [
                'settings' => [
                    'branding' => [
                        'primary_color' => $this->colorPrimary,
                        'secondary_color' => $this->colorSecondary,
                    ],
                ],
            ]
        );

        $userMetaStripe = new UserMetaStripe($stripeAccountIdUserMeta->user);
        $userMetaStripe->setAccount($stripeAccount);
    }
}
