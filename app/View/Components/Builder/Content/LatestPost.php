<?php

namespace App\View\Components\Builder\Content;

use App\Services\CategoryService;
use App\Services\PostService;

class LatestPost extends BaseContent
{
    public $posts;
    public $limit;

    private $config;

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->config = $this->entity['config'];

        $this->limit = $this->getLimit();
        $this->posts = $this->getPosts();
    }

    private function getPosts()
    {
        return app(PostService::class)->getLatestPost(
            $this->limit,
            $this->getCategoryIds(),
        );
    }

    private function getLimit(): int
    {
        $limit = $this->config['post']['limit'];

        if (!$limit || $limit > 6 || $limit < 1) {
            return 3;
        }

        return $limit;
    }

    private function getCategoryIds()
    {
        $categoryId = $this->config['post']['categoryId'];

        return array_filter([
            $categoryId
        ]);
    }
}
