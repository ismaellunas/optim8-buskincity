<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class FixMediaUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media-user {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill the user id on media';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $media = Media::all();
        $userId = $this->option('user');

        foreach ($media as $medium) {
            $medium->user_id = $userId;

            if (
                $this->startWith($medium->file_name, $this->getPrefix() . 'settings')
                || $this->startWith($medium->file_name, $this->getPrefix() . 'product')
                || $this->startWith($medium->file_name, $this->getPrefix() . 'space')
            ) {
                $medium->type = 0;
            }

            if ($this->startWith($medium->file_name, $this->getPrefix() . 'profiles')) {
                $medium->user_id = $medium->medially_id;
            }

            if ($this->startWith($medium->file_name, $this->getPrefix() . 'form_builder_assets')) {
                $medium->user_id = null;
            }

            $medium->save();

        }

        return Command::SUCCESS;
    }

    private function startWith(string $text, string $prefix): bool
    {
        return Str::of($text)->startsWith($prefix);
    }

    private function getPrefix()
    {
        return (!App::environment('production') ? config('app.env').'_' : null);
    }
}
