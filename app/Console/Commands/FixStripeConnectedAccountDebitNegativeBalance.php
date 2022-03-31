<?php

namespace App\Console\Commands;

use App\Models\UserMeta;
use App\Services\StripeService;
use Illuminate\Console\Command;

class FixStripeConnectedAccountDebitNegativeBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:stripe-connected-account-debit-negative-balance '.
        '{--id=* : Stripe Connected Account ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix `debit_negative_balance` to `false` for existing stripe connected account';

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
        $idOptions = $this->option('id');

        $stripeAccountIdUserMetas = UserMeta::where('key', 'stripe_account_id')
            ->when($idOptions, function ($query, $idOptions) {
                $query->whereIn('value', $idOptions);
            })
            ->get(['id', 'key', 'value', 'user_id']);

        $stripeAccountIdUserMetas->each([$this, 'updateConnectedAccount']);

        return 0;
    }

    public function updateConnectedAccount(UserMeta $stripeAccountIdUserMeta)
    {
        $updateData = [
            'settings' => [
                'payouts' => [
                    'debit_negative_balances' => false,
                ],
            ],
        ];

        if ($this->output->isVerbose()) {
            $this->info('----------');
            $this->info('Stripe Account : '.$stripeAccountIdUserMeta->value);
            $this->info('User ID        : '.$stripeAccountIdUserMeta->user_id);
        }

        try {
            $stripeAccount = app(StripeService::class)->updateAccount(
                $stripeAccountIdUserMeta->value,
                $updateData
            );

            $userMeta = UserMeta::firstOrNew([
                'key' => 'stripe_account',
                'user_id' => $stripeAccountIdUserMeta->user_id
            ]);

            $userMeta->value = json_encode($stripeAccount);
            $userMeta->type = 'object';
            $userMeta->save();

        } catch (\Exception $e) {
            $this->error('An error occurred while updating Stripe Account: '.$stripeAccountIdUserMeta->value);
            $this->error($e->getMessage());
        }
    }
}
