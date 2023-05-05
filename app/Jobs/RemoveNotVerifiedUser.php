<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveNotVerifiedUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now()
            ->subMonths(1)
            ->format('Y-m-d');

        User::whereNull('email_verified_at')
            ->where('created_at', '<=', $date . ' 00:00:00')
            ->whereDoesntHave('connectedAccounts')
            ->forceDelete();
    }
}
