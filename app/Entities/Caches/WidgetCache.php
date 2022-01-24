<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

class WidgetCache extends BaseCache
{
    protected string $tag = 'widgets';

    private function getWidgetTag(string $widget): string
    {
        return $this->tag . ":" . $widget;
    }

    public function remember(string $key, Closure $callback): mixed
    {
        return Cache::tags(
                $this->tag,
                $this->getWidgetTag($key),
            )->rememberForever(
                $key,
                $callback
            );
    }

    public function flushWidget(string $widget): bool
    {
        return Cache::tags($this->getWidgetTag($widget))->flush();
    }
}
