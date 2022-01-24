<?php

namespace App\Observers;

use App\Jobs\ProcessPublishScheduledPost;
use App\Models\Post;
use App\Services\WidgetService;

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

        app(WidgetService::class)->flushWidget("post");
    }

    public function deleted()
    {
        app(WidgetService::class)->flushWidget("post");
    }
}
