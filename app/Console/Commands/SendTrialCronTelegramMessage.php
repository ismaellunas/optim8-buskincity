<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTrialCronTelegramMessage extends Command
{
    protected $token;
    protected $chatId;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-trial-cron-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->token = "1854831763:AAFXIpqFYelQHmritactRfxLGT8-KhQ11Ro";
        $this->chatId = "198178312";
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 15);
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query([
            'chat_id' => $this->chatId,
            'text' => "run on ".date("Y-m-d")." at ".date("H:i:s"),
        ]));

        curl_exec($handle);

        return 0;
    }
}
