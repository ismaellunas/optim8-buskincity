<?php

namespace App\Console\Commands;

use App\Jobs\SendResetPasswordLink;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ProdPreparationUserPerformerSendResetPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prod-preparation:user-performer-send-reset-password-link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production preparation, send reset password link to every performer has been imported.';

    public function isEnabled()
    {
        return env('PRODUCTION_PREPARATION', false);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->confirm('Are you sure want to send a link to reset password for the new performer?')) {
            $chunkEmails = $this->getEmailNewPerformers();

            $currentDate = now();
            $second = 15;

            foreach ($chunkEmails as $emails) {
                SendResetPasswordLink::dispatch($emails)
                    ->delay($currentDate->addSeconds($second));

                $second += 15;
            }
        }

        return Command::SUCCESS;
    }

    private function getEmailNewPerformers(): Collection
    {
        return User::inRoleNames([
                config('permission.role_names.performer')
            ])
            ->emailVerified()
            ->whereNull('password')
            ->get(['email'])
            ->pluck('email')
            ->chunk(5);
    }
}
