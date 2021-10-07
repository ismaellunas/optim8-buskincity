<?php

namespace App\Observers;

use App\Models\Post;
use App\Jobs\ProcessPublishScheduledPost;

class PostObserver
{
    public function creating(Post $post)
    {
        if (auth()->check()) {
            $post->author_id = auth()->id();
        }
    }

    public function saved(Post $post)
    {
        if ($post->isChangedToScheduled) {
            ProcessPublishScheduledPost::dispatch($post)
                ->delay($post->scheduled_at);
        }
    }

    /**
     * Handle the Post "created" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function created(Post $post)
    {
        //
    }

    /**
     * Handle the Post "updated" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function updated(Post $post)
    {
        //
    }

    /**
     * Handle the Post "deleted" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function deleted(Post $post)
    {
        //
    }

    /**
     * Handle the Post "restored" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function restored(Post $post)
    {
        //
    }

    /**
     * Handle the Post "force deleted" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function forceDeleted(Post $post)
    {
        //
    }
}
