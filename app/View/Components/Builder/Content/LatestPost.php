<?php

namespace App\View\Components\Builder\Content;

use App\Services\CategoryService;
use App\Services\PostService;

class LatestPost extends BaseContent
{
    public $posts;

    private $config;

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->config = $this->entity['config'];
        $this->posts = $this->getPosts();
    }

    private function getPosts()
    {
        return app(PostService::class)->getLatestPost(
            $this->getLimit(),
            $this->getCategoryIds(),
        );
    }

    private function getLimit(): int
    {
        $limit = $this->config['post']['limit'];

        return $limit > 9 ? 9 : $limit;
    }

    private function getCategoryIds()
    {
        $categoryId = $this->config['post']['categoryId'];

        return array_filter([
            $categoryId
        ]);
    }
}
