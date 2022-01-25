<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

class WidgetCache extends BaseCache
{
    protected string $tag = 'widgets';
    private string $key;

    private function getWidgetTag(string $widget): string
    {
        return $this->tag . ":" . $widget;
    }

    protected function getTags(): array
    {
        return array_merge(
            parent::getTags(),
            [
                $this->getWidgetTag($this->key),
            ]
        );
    }

    public function remember(string $key, Closure $callback): mixed
    {
        $this->key = $key;

        return parent::remember($key, $callback);
    }

    public function flushWidget(string $widget): bool
    {
        return Cache::tags($this->getWidgetTag($widget))->flush();
    }
}
