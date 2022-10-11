<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class FixPostPublishedAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:post-published-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix data posts have status published.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $posts = Post::where('status', Post::STATUS_PUBLISHED)->get();

        foreach ($posts as $post) {
            $post->published_at = $post->updated_at;
            $post->save();
        }

        return Command::SUCCESS;
    }
}
