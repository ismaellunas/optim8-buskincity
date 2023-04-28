<?php

namespace App\Jobs;

use App\Mail\ResetPasswordPerformer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendResetPasswordLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Collection $emails,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->emails as $email) {
            $token = Str::random(64);

            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => bcrypt($token),
                'created_at' => Carbon::now(),
            ]);

            $url = route('password.reset', [
                'token' => $token,
                'email' => $email,
            ]);

            Mail::to($email)
                ->send(new ResetPasswordPerformer($url));
        }
    }
}
