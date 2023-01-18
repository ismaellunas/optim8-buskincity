<?php

namespace App\Observers;

use App\Jobs\ProcessPublishScheduledPost;
use App\Models\Post;
use App\Services\MenuService;

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

    public function updated(Post $post)
    {
        if ($post->isChangedToUnpublished) {
            app(MenuService::class)->removeModelFromMenus($post);
        }
    }

    public function deleted(Post $post)
    {
        app(MenuService::class)->removeModelFromMenus($post);
    }
}
