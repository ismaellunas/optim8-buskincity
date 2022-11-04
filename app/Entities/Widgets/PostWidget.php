<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\Caches\WidgetCache;
use App\Models\Post;
use App\Services\StorageService;
use App\Services\WidgetService;

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
            'permissions' => $this->getPermissions(),
        ];
    }

    private function getRecords(): array
    {
        return app(WidgetCache::class)->remember(
            app(WidgetService::class)->getWidgetName('post'),
            function () {
                $records = Post::latest()
                    ->with([
                        'coverImage' => function ($query) {
                            $query->select([
                                'id',
                                'extension',
                                'file_name',
                                'file_url',
                                'version',
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
                    ->get([
                        'id',
                        'cover_image_id',
                        'locale',
                        'title',
                    ]);

                $this->transformRecords($records);

                return $records->all();
            }
        );
    }

    private function transformRecords($records): void
    {
        $records->transform(function ($record) {
            return [
                'id' => $record->id,
                'categories' => $record->categories->map(function ($category) {
                    return $category->firstTranslationName;
                }),
                'locale' => $record->locale,
                'title' => $record->title,
                'thumbnail_url' => $record->coverImage->thumbnailUrl
                    ?? StorageService::getImageUrl(
                        config('constants.default_images.widget_post_thumbnail')
                    ),
            ];
        });
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('post.browse');
    }

    private function getPermissions(): array
    {
        return [
            'add' => $this->user->can('post.add'),
            'delete' => $this->user->can('post.delete'),
            'edit' => $this->user->can('post.edit'),
        ];
    }
}