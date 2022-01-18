<?php

namespace App\Observers;

use App\Entities\Caches\WidgetCache;
use App\Jobs\ProcessPublishScheduledPost;
use App\Models\Post;

class PostObserver
{
    public function __construct()
    {
        app(WidgetCache::class)->flushWidget(config('constants.widget_cache.post'));
    }

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
}
