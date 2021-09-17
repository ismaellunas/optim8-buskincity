<?php

namespace App\Jobs;

//use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPublishScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = Carbon::now('UTC');

        $scheduledPosts = Post::scheduled()
            ->where('scheduled_at', '<=', $now->toDateTimeString())
            ->get();

        foreach ($scheduledPosts as $scheduledPost) {
            $scheduledPost->publish();
        }
    }
}
