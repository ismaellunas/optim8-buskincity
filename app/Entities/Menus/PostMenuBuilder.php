<?php

namespace App\Entities\Menus;

use App\Contracts\MenuBuilderInterface;
use App\Models\Post;
use Illuminate\Support\Str;

class PostMenuBuilder implements MenuBuilderInterface
{
    protected $key = 'post';

    public function getKey(): string
    {
        return Str::lower($this->getTypeOptions()['id'] ?? $this->key);
    }

    public function getTypeOptions(): array
    {
        return [
            'id' => 'post',
            'value' => 'Post',
            'model' => 'App\Models\Post',
        ];
    }

    public function getMenuOptions(): array
    {
        return Post::published()
            ->get([
                'id',
                'locale',
                'title',
            ])
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'value' => $post->title,
                    'locale' => $post->locale,
                ];
            })
            ->all();
    }

    public function isOptionDisplayed(): bool
    {
        return true;
    }
}