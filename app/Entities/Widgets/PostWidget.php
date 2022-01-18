<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\Caches\WidgetCache;
use App\Models\Post;

class PostWidget implements WidgetInterface
{
    protected $baseRouteName = "admin.posts";
    protected $componentName = "Post";
    protected $data = [];
    protected $recordLimit = 3;
    protected $title = "Latest Articles";
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->data = $this->getWidgetData();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
        ];
    }

    private function getWidgetData(): array
    {
        return [
            'baseRouteName' => $this->baseRouteName,
            'records' => $this->getRecords(),
            'actionAccessed' => $this->actionAccessed(),
        ];
    }

    private function getRecords(): array
    {
        return app(WidgetCache::class)->remember(
            config('constants.widget_cache.post'),
            function () {
                $records = Post::latest()
                    ->with([
                        'coverImage' => function ($query) {
                            $query->select([
                                'id',
                                'file_name',
                                'file_url',
                            ]);
                        },
                        'categories' => function ($query) {
                            $query->select(['categories.id']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select([
                                        'id',
                                        'name',
                                        'category_id',
                                        'locale',
                                    ]);
                                },
                            ]);
                        },
                    ])
                    ->limit($this->recordLimit)
                    ->get();

                $this->transformRecords($records);

                return $records->all();
            }
        );
    }

    private function transformRecords($records): void
    {
        $records->transform(function ($record) {
            $record->thumbnail_url = (
                $record->coverImage
                ? $record->coverImage->thumbnailUrl
                : null
            );

            $record->categories->transform(function ($category) {
                $category->name = $category->name ?? $category->translations[0]->name;

                return $category;
            });

            return $record;
        });
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('post.browse');
    }

    private function actionAccessed(): array
    {
        return [
            'add' => $this->user->can('post.add'),
            'delete' => $this->user->can('post.delete'),
            'edit' => $this->user->can('post.edit'),
        ];
    }
}